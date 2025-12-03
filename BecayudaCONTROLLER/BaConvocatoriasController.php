<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Becayuda\BaConvocatorias;

class BaConvocatoriasController extends Controller
{
    /**
     * Lista de convocatorias
     */
    public function index()
    {
        $convocatorias = BaConvocatorias::orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'data'   => $convocatorias,
        ]);
    }

    /**
     * Crear una nueva convocatoria
     */
    public function store(Request $request)
    {
        try {
            // VALIDACIÓN
            $validated = $request->validate([
                'id_periodo'        => ['required', 'integer'],
                'nombre'            => ['required', 'string', 'max:255'],
                'descripcion'       => ['nullable', 'string'],
                'tipo_convocatoria' => [
                    'required',
                    'string',
                    // solo 'abierta' o 'focalizada'
                    Rule::in(['abierta', 'focalizada']),
                ],
                'fecha_inicio'      => ['required', 'date'],
                'fecha_fin'         => ['required', 'date', 'after_or_equal:fecha_inicio'],
                'estado'            => ['required', 'boolean'],
            ]);

            // Verificamos que el periodo exista en becayuda.ba_periodos
            $periodoExiste = DB::connection('pgsql')
                ->table('becayuda.ba_periodos')
                ->where('id', $validated['id_periodo'])
                ->exists();

            if (!$periodoExiste) {
                return response()->json([
                    'status'  => false,
                    'message' => 'El periodo seleccionado no existe',
                    'errors'  => [
                        'id_periodo' => ['El periodo indicado no existe en la base de datos.'],
                    ],
                ], 422);
            }

            // Usuario autenticado (opcional, si usas Sanctum)
            $userId = optional($request->user())->id;

            // Datos a guardar
            $data = [
                'id_periodo'        => $validated['id_periodo'],
                'nombre'            => $validated['nombre'],
                'descripcion'       => $validated['descripcion'] ?? null,
                'tipo_convocatoria' => $validated['tipo_convocatoria'],
                'fecha_inicio'      => $validated['fecha_inicio'],
                'fecha_fin'         => $validated['fecha_fin'],
                'estado'            => $validated['estado'],
            ];

            if ($userId) {
                $data['id_user_created'] = $userId;
                $data['id_user_updated'] = $userId;
            }

            // INSERT a la tabla becayuda.ba_convocatorias
            $convocatoria = BaConvocatorias::create($data);

            return response()->json([
                'status'  => true,
                'message' => 'Convocatoria creada correctamente',
                'data'    => $convocatoria,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Errores de validación',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error('Error creando convocatoria: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Error interno al crear la convocatoria',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar una convocatoria específica
     */
    public function show($convocatoria)
    {
        $convocatoria = BaConvocatorias::find($convocatoria);

        if (!$convocatoria) {
            return response()->json([
                'status'  => false,
                'message' => 'Convocatoria no encontrada',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $convocatoria,
        ]);
    }

    /**
     * Actualizar una convocatoria
     */
    public function update(Request $request, $convocatoria)
    {
        try {
            $convocatoria = BaConvocatorias::find($convocatoria);

            if (!$convocatoria) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Convocatoria no encontrada',
                ], 404);
            }

            $validated = $request->validate([
                'id_periodo'        => ['sometimes', 'integer'],
                'nombre'            => ['sometimes', 'string', 'max:255'],
                'descripcion'       => ['nullable', 'string'],
                'tipo_convocatoria' => [
                    'sometimes',
                    'string',
                    Rule::in(['abierta', 'focalizada']),
                ],
                'fecha_inicio'      => ['sometimes', 'date'],
                'fecha_fin'         => ['sometimes', 'date', 'after_or_equal:fecha_inicio'],
                'estado'            => ['sometimes', 'boolean'],
            ]);

            // Si viene id_periodo, verificar que exista
            if (isset($validated['id_periodo'])) {
                $periodoExiste = DB::connection('pgsql')
                    ->table('becayuda.ba_periodos')
                    ->where('id', $validated['id_periodo'])
                    ->exists();

                if (!$periodoExiste) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'El periodo seleccionado no existe',
                        'errors'  => [
                            'id_periodo' => ['El periodo indicado no existe en la base de datos.'],
                        ],
                    ], 422);
                }
            }

            $userId = optional($request->user())->id;
            if ($userId) {
                $validated['id_user_updated'] = $userId;
            }

            $convocatoria->fill($validated);
            $convocatoria->save();

            return response()->json([
                'status'  => true,
                'message' => 'Convocatoria actualizada correctamente',
                'data'    => $convocatoria,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Errores de validación',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error('Error actualizando convocatoria: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Error interno al actualizar la convocatoria',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar una convocatoria
     */
    public function destroy($convocatoria)
    {
        try {
            $convocatoria = BaConvocatorias::find($convocatoria);

            if (!$convocatoria) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Convocatoria no encontrada',
                ], 404);
            }

            $convocatoria->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Convocatoria eliminada correctamente',
            ]);

        } catch (\Throwable $e) {

            Log::error('Error eliminando convocatoria: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Error interno al eliminar la convocatoria',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
