<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaComisionBecas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaComisionBecasController extends Controller
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
            $items = BaComisionBecas::with('miembros')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener comisiones: '.$e->getMessage());
            return $this->respondError('Error al obtener comisiones', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'       => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            'estado'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaComisionBecas::create($request->all());

            return $this->respond($item, 'Comisión creada exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando comisión: '.$e->getMessage());
            return $this->respondError('Error al crear comisión', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaComisionBecas::with('miembros')->findOrFail($id);
            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Comisión no encontrada', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre'       => 'sometimes|required|string|max:255',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin'    => 'sometimes|nullable|date|after_or_equal:fecha_inicio',
            'estado'       => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaComisionBecas::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Comisión actualizada correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando comisión: '.$e->getMessage());
            return $this->respondError('Error al actualizar comisión', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaComisionBecas::findOrFail($id);

            if ($item->miembros()->exists()) {
                return $this->respondError('No se puede eliminar: tiene miembros asociados', 409);
            }

            $item->delete();
            return $this->respond(null, 'Comisión eliminada correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando comisión: '.$e->getMessage());
            return $this->respondError('Error al eliminar comisión', 500);
        }
    }
}
