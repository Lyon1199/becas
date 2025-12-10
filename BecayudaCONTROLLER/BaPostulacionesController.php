<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaPostulaciones;
use App\Models\Becayuda\BaBeneficios;
use App\Models\Becayuda\BaConvocatorias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BaPostulacionesController extends Controller
{
    private function respond($data = null, $message = '', $status = 200)
    {
        return response()->json([
            'success' => $status >= 200 && $status < 300,
            'data'    => $data,
            'message' => $message,
        ], $status);
    }

    private function respondError($message, $status = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'data'    => $data,
            'message' => $message,
        ], $status);
    }

    // LISTAR TODAS
    public function index()
    {
        try {
            $items = BaPostulaciones::with([
                    'beneficio',
                    'convocatoria',
                    'persona',
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener postulaciones: ' . $e->getMessage());
            return $this->respondError('Error al obtener postulaciones', 500);
        }
    }

    // CREAR
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_persona'        => ['required', 'integer'],
            'id_beneficio'      => [
                'required',
                'integer',
                Rule::exists(BaBeneficios::class, 'id'),
            ],
            'id_convocatoria'   => [
                'required',
                'integer',
                Rule::exists(BaConvocatorias::class, 'id'),
            ],
            'fecha_postulacion' => ['sometimes', 'nullable', 'date'],
            // si llega, solo validamos que sea string
            'estado_postulacion'=> ['sometimes', 'nullable', 'string', 'max:100'],
            'promedio'          => ['nullable', 'numeric'],
            'observaciones'     => ['nullable', 'string'],
            'id_user_created'   => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $data = $request->all();

            // Si no llega fecha, usamos la actual
            if (empty($data['fecha_postulacion'])) {
                $data['fecha_postulacion'] = now();
            }

            // --- DEFAULT Y NORMALIZACIÓN DE ESTADO ---
            // Si NO viene estado_postulacion (o viene vacío / null), lo ponemos en 'pendiente'
            if (
                !array_key_exists('estado_postulacion', $data) ||
                $data['estado_postulacion'] === null ||
                $data['estado_postulacion'] === ''
            ) {
                $data['estado_postulacion'] = 'pendiente';
            } else {
                // normalizamos a minúsculas para cumplir con el CHECK de PostgreSQL
                $estado = strtolower(trim($data['estado_postulacion']));

                $permitidos = [
                    'pendiente',
                    'en_validacion',
                    'en_correccion',
                    'aprobada',
                    'denegada',
                    'desistida',
                    'renunciada',
                ];

                if (!in_array($estado, $permitidos, true)) {
                    return $this->respondError('El estado_postulacion no es válido.', 422);
                }

                $data['estado_postulacion'] = $estado;
            }
            // --- FIN ESTADO ---

            $data['id_user_updated'] = $data['id_user_created'];

            $item = BaPostulaciones::create($data);

            return $this->respond($item, 'Postulación creada exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando postulación: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);
            return $this->respondError('Error al crear postulación', 500);
        }
    }

    // VER UNA
    public function show($id)
    {
        try {
            $item = BaPostulaciones::with([
                    'beneficio',
                    'convocatoria',
                    'persona',
                    'documentos',
                    'informes',
                    'asignaciones',
                ])
                ->findOrFail($id);

            return $this->respond($item);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->respondError('Postulación no encontrada', 404);
        } catch (\Throwable $e) {
            Log::error('Error al obtener postulación: ' . $e->getMessage());
            return $this->respondError('Error al obtener postulación', 500);
        }
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_persona'        => ['sometimes', 'required', 'integer'],
            'id_beneficio'      => [
                'sometimes',
                'required',
                'integer',
                Rule::exists(BaBeneficios::class, 'id'),
            ],
            'id_convocatoria'   => [
                'sometimes',
                'required',
                'integer',
                Rule::exists(BaConvocatorias::class, 'id'),
            ],
            'fecha_postulacion' => ['sometimes', 'nullable', 'date'],
            'estado_postulacion'=> ['sometimes', 'required', 'string', 'max:100'],
            'promedio'          => ['sometimes', 'nullable', 'numeric'],
            'observaciones'     => ['sometimes', 'nullable', 'string'],
            'id_user_updated'   => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaPostulaciones::findOrFail($id);
            $data = $request->all();

            // Si en el update viene estado_postulacion, lo normalizamos también
            if (array_key_exists('estado_postulacion', $data)) {
                $estado = strtolower(trim($data['estado_postulacion']));

                $permitidos = [
                    'pendiente',
                    'en_validacion',
                    'en_correccion',
                    'aprobada',
                    'denegada',
                    'desistida',
                    'renunciada',
                ];

                if (!in_array($estado, $permitidos, true)) {
                    return $this->respondError('El estado_postulacion no es válido.', 422);
                }

                $data['estado_postulacion'] = $estado;
            }

            $item->update($data);

            return $this->respond($item, 'Postulación actualizada correctamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->respondError('Postulación no encontrada', 404);
        } catch (\Throwable $e) {
            Log::error('Error actualizando postulación: ' . $e->getMessage());
            return $this->respondError('Error al actualizar postulación', 500);
        }
    }

    // ELIMINAR
    public function destroy($id)
    {
        try {
            $item = BaPostulaciones::findOrFail($id);

            if (
                $item->documentos()->exists() ||
                $item->informes()->exists()  ||
                $item->asignaciones()->exists()
            ) {
                return $this->respondError(
                    'No se puede eliminar: tiene documentos, informes o asignaciones asociadas',
                    409
                );
            }

            $item->delete();

            return $this->respond(null, 'Postulación eliminada correctamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->respondError('Postulación no encontrada', 404);
        } catch (\Throwable $e) {
            Log::error('Error eliminando postulación: ' . $e->getMessage());
            return $this->respondError('Error al eliminar postulación', 500);
        }
    }
}
