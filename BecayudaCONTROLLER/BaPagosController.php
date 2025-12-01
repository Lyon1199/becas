<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaPagos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaPagosController extends Controller
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
            $items = BaPagos::with('asignacion')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener pagos: '.$e->getMessage());
            return $this->respondError('Error al obtener pagos', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_asignacion'  => 'required|exists:becayuda.ba_asignaciones,id',
            'valor_pagado'   => 'required|numeric',
            'fecha_pago'     => 'required|date',
            'estado_pago'    => 'required|string|max:100',
            'observacion'    => 'nullable|string',
            'id_user_created'=> 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaPagos::create($request->all());

            return $this->respond($item, 'Pago registrado exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando pago: '.$e->getMessage());
            return $this->respondError('Error al registrar pago', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaPagos::with('asignacion')->findOrFail($id);
            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Pago no encontrado', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_asignacion'  => 'sometimes|required|exists:becayuda.ba_asignaciones,id',
            'valor_pagado'   => 'sometimes|required|numeric',
            'fecha_pago'     => 'sometimes|required|date',
            'estado_pago'    => 'sometimes|required|string|max:100',
            'observacion'    => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaPagos::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Pago actualizado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando pago: '.$e->getMessage());
            return $this->respondError('Error al actualizar pago', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaPagos::findOrFail($id);
            $item->delete();

            return $this->respond(null, 'Pago eliminado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando pago: '.$e->getMessage());
            return $this->respondError('Error al eliminar pago', 500);
        }
    }
}