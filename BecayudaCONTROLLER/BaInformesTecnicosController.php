<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaInformesTecnicos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaInformesTecnicosController extends Controller
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
            $items = BaInformesTecnicos::with('postulacion')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener informes: '.$e->getMessage());
            return $this->respondError('Error al obtener informes', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_postulacion' => 'required|exists:becayuda.ba_postulaciones,id',
            'tipo_informe'   => 'required|string|max:100',
            'resultado'      => 'required|string',
            'calificacion'   => 'nullable|numeric',
            'fecha_emision'  => 'required|date',
            'responsable'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaInformesTecnicos::create($request->all());

            return $this->respond($item, 'Informe técnico creado exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando informe: '.$e->getMessage());
            return $this->respondError('Error al crear informe', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaInformesTecnicos::with('postulacion')->findOrFail($id);
            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Informe no encontrado', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_postulacion' => 'sometimes|required|exists:becayuda.ba_postulaciones,id',
            'tipo_informe'   => 'sometimes|required|string|max:100',
            'resultado'      => 'sometimes|required|string',
            'calificacion'   => 'sometimes|nullable|numeric',
            'fecha_emision'  => 'sometimes|required|date',
            'responsable'    => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaInformesTecnicos::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Informe actualizado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando informe: '.$e->getMessage());
            return $this->respondError('Error al actualizar informe', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaInformesTecnicos::findOrFail($id);
            $item->delete();

            return $this->respond(null, 'Informe eliminado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando informe: '.$e->getMessage());
            return $this->respondError('Error al eliminar informe', 500);
        }
    }
}
