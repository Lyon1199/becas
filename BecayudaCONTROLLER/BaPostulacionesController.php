<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaPostulaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

    public function index()
    {
        try {
            $items = BaPostulaciones::with(['beneficio', 'convocatoria', 'persona'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener postulaciones: '.$e->getMessage());
            return $this->respondError('Error al obtener postulaciones', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_persona'        => 'required|integer',
            'id_beneficio'      => 'required|exists:becayuda.ba_beneficios,id',
            'id_convocatoria'   => 'required|exists:becayuda.ba_convocatorias,id',
            'fecha_postulacion' => 'required|date',
            'estado_postulacion'=> 'required|string|max:100',
            'promedio'          => 'nullable|numeric',
            'observaciones'     => 'nullable|string',
            'id_user_created'   => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $data = $request->all();
            $data['id_user_updated'] = $data['id_user_created'];

            $item = BaPostulaciones::create($data);

            return $this->respond($item, 'Postulación creada exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando postulación: '.$e->getMessage());
            return $this->respondError('Error al crear postulación', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaPostulaciones::with(['beneficio', 'convocatoria', 'persona', 'documentos', 'informes', 'asignaciones'])
                ->findOrFail($id);

            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Postulación no encontrada', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_persona'        => 'sometimes|required|integer',
            'id_beneficio'      => 'sometimes|required|exists:becayuda.ba_beneficios,id',
            'id_convocatoria'   => 'sometimes|required|exists:becayuda.ba_convocatorias,id',
            'fecha_postulacion' => 'sometimes|required|date',
            'estado_postulacion'=> 'sometimes|required|string|max:100',
            'promedio'          => 'sometimes|nullable|numeric',
            'observaciones'     => 'sometimes|nullable|string',
            'id_user_updated'   => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaPostulaciones::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Postulación actualizada correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando postulación: '.$e->getMessage());
            return $this->respondError('Error al actualizar postulación', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaPostulaciones::findOrFail($id);

            if ($item->documentos()->exists() || $item->informes()->exists() || $item->asignaciones()->exists()) {
                return $this->respondError(
                    'No se puede eliminar: tiene documentos, informes o asignaciones asociadas',
                    409
                );
            }

            $item->delete();
            return $this->respond(null, 'Postulación eliminada correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando postulación: '.$e->getMessage());
            return $this->respondError('Error al eliminar postulación', 500);
        }
    }
}
    