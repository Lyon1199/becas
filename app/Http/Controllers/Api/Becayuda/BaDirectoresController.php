<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaDirectores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaDirectoresController extends Controller
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
            $items = BaDirectores::orderBy('created_at', 'desc')->get();
            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener directores: '.$e->getMessage());
            return $this->respondError('Error al obtener directores', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombres'        => 'required|string|max:255',
            'cargo'          => 'required|string|max:255',
            'estado'         => 'required|boolean',
            'id_user_created'=> 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $data = $request->all();
            $data['id_user_updated'] = $data['id_user_created'];

            $item = BaDirectores::create($data);

            return $this->respond($item, 'Director creado exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando director: '.$e->getMessage());
            return $this->respondError('Error al crear director', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaDirectores::findOrFail($id);
            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Director no encontrado', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombres'        => 'sometimes|required|string|max:255',
            'cargo'          => 'sometimes|required|string|max:255',
            'estado'         => 'sometimes|required|boolean',
            'id_user_updated'=> 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaDirectores::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Director actualizado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando director: '.$e->getMessage());
            return $this->respondError('Error al actualizar director', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaDirectores::findOrFail($id);
            $item->delete();

            return $this->respond(null, 'Director eliminado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando director: '.$e->getMessage());
            return $this->respondError('Error al eliminar director', 500);
        }
    }
}
