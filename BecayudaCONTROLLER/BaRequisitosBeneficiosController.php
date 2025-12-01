<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaRequisitosBeneficios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaRequisitosBeneficiosController extends Controller
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
            $items = BaRequisitosBeneficios::with(['beneficio', 'convocatoria'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener requisitos: '.$e->getMessage());
            return $this->respondError('Error al obtener requisitos', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_beneficio'         => 'required|exists:becayuda.ba_beneficios,id',
            'id_convocatoria'      => 'required|exists:becayuda.ba_convocatorias,id',
            'descripcion_requisito'=> 'required|string|max:500',
            'quien_sube_requisito' => 'nullable|string|max:255',
            'obligatorio'          => 'required|boolean',
            'estado'               => 'required|boolean',
            'id_user_created'      => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $data = $request->all();
            $data['id_user_updated'] = $data['id_user_created'];

            $item = BaRequisitosBeneficios::create($data);

            return $this->respond($item, 'Requisito creado exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando requisito: '.$e->getMessage());
            return $this->respondError('Error al crear requisito', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaRequisitosBeneficios::with(['beneficio', 'convocatoria'])->findOrFail($id);
            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Requisito no encontrado', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_beneficio'         => 'sometimes|required|exists:becayuda.ba_beneficios,id',
            'id_convocatoria'      => 'sometimes|required|exists:becayuda.ba_convocatorias,id',
            'descripcion_requisito'=> 'sometimes|required|string|max:500',
            'quien_sube_requisito' => 'sometimes|nullable|string|max:255',
            'obligatorio'          => 'sometimes|required|boolean',
            'estado'               => 'sometimes|required|boolean',
            'id_user_updated'      => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaRequisitosBeneficios::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Requisito actualizado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando requisito: '.$e->getMessage());
            return $this->respondError('Error al actualizar requisito', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaRequisitosBeneficios::findOrFail($id);
            $item->delete();

            return $this->respond(null, 'Requisito eliminado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando requisito: '.$e->getMessage());
            return $this->respondError('Error al eliminar requisito', 500);
        }
    }
}
