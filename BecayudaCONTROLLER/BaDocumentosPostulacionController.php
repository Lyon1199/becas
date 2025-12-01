<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaDocumentosPostulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaDocumentosPostulacionController extends Controller
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
            $items = BaDocumentosPostulacion::with('postulacion')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener documentos: '.$e->getMessage());
            return $this->respondError('Error al obtener documentos', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_postulacion'  => 'required|exists:becayuda.ba_postulaciones,id',
            'nombre_documento'=> 'required|string|max:255',
            'ruta_archivo'    => 'required|string|max:500',
            'estado_revision' => 'nullable|string|max:100',
            'observacion'     => 'nullable|string',
            'id_user_revisor' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaDocumentosPostulacion::create($request->all());

            return $this->respond($item, 'Documento registrado exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando documento: '.$e->getMessage());
            return $this->respondError('Error al crear documento', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaDocumentosPostulacion::with('postulacion')->findOrFail($id);
            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Documento no encontrado', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_postulacion'  => 'sometimes|required|exists:becayuda.ba_postulaciones,id',
            'nombre_documento'=> 'sometimes|required|string|max:255',
            'ruta_archivo'    => 'sometimes|required|string|max:500',
            'estado_revision' => 'sometimes|nullable|string|max:100',
            'observacion'     => 'sometimes|nullable|string',
            'id_user_revisor' => 'sometimes|nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaDocumentosPostulacion::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Documento actualizado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando documento: '.$e->getMessage());
            return $this->respondError('Error al actualizar documento', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaDocumentosPostulacion::findOrFail($id);
            $item->delete();

            return $this->respond(null, 'Documento eliminado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando documento: '.$e->getMessage());
            return $this->respondError('Error al eliminar documento', 500);
        }
    }
}
