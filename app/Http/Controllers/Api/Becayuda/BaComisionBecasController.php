<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaFasesConvocatoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaFasesConvocatoriaController extends Controller
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
            $items = BaFasesConvocatoria::with('convocatoria')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener fases: '.$e->getMessage());
            return $this->respondError('Error al obtener fases', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_convocatoria'=> 'required|exists:becayuda.ba_convocatorias,id',
            'nombre_fase'    => 'required|string|max:255',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'descripcion'    => 'nullable|string',
            'orden'          => 'nullable|integer',
            'estado'         => 'required|boolean',
            'id_user_created'=> 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $data = $request->all();
            $data['id_user_updated'] = $data['id_user_created'];

            $item = BaFasesConvocatoria::create($data);

            return $this->respond($item, 'Fase creada exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando fase: '.$e->getMessage());
            return $this->respondError('Error al crear fase', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaFasesConvocatoria::with('convocatoria')->findOrFail($id);
            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Fase no encontrada', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_convocatoria'=> 'sometimes|required|exists:becayuda.ba_convocatorias,id',
            'nombre_fase'    => 'sometimes|required|string|max:255',
            'fecha_inicio'   => 'sometimes|required|date',
            'fecha_fin'      => 'sometimes|required|date|after_or_equal:fecha_inicio',
            'descripcion'    => 'sometimes|nullable|string',
            'orden'          => 'sometimes|nullable|integer',
            'estado'         => 'sometimes|required|boolean',
            'id_user_updated'=> 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaFasesConvocatoria::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Fase actualizada correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando fase: '.$e->getMessage());
            return $this->respondError('Error al actualizar fase', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaFasesConvocatoria::findOrFail($id);
            $item->delete();

            return $this->respond(null, 'Fase eliminada correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando fase: '.$e->getMessage());
            return $this->respondError('Error al eliminar fase', 500);
        }
    }
}
