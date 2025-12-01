<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaBeneficios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaBeneficiosController extends Controller
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
            $items = BaBeneficios::orderBy('created_at', 'desc')->get();
            return $this->respond($items);
        } catch (\Throwable $e) {
            Log::error('Error al obtener beneficios: '.$e->getMessage());
            return $this->respondError('Error al obtener beneficios', 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion_sistema'  => 'required|string|max:500',
            'descripcion_senescyt' => 'nullable|string|max:500',
            'tipo_beneficio'       => 'required|string|max:100',
            'requisitos_unicos'    => 'nullable',
            'estado'               => 'required|boolean',
            'id_user_created'      => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $data = $request->all();
            $data['id_user_updated'] = $data['id_user_created'];

            $item = BaBeneficios::create($data);

            return $this->respond($item, 'Beneficio creado exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando beneficio: '.$e->getMessage());
            return $this->respondError('Error al crear beneficio', 500);
        }
    }

    public function show($id)
    {
        try {
            $item = BaBeneficios::findOrFail($id);
            return $this->respond($item);
        } catch (\Throwable $e) {
            return $this->respondError('Beneficio no encontrado', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'descripcion_sistema'  => 'sometimes|required|string|max:500',
            'descripcion_senescyt' => 'sometimes|nullable|string|max:500',
            'tipo_beneficio'       => 'sometimes|required|string|max:100',
            'requisitos_unicos'    => 'sometimes|nullable',
            'estado'               => 'sometimes|required|boolean',
            'id_user_updated'      => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $item = BaBeneficios::findOrFail($id);
            $item->update($request->all());

            return $this->respond($item, 'Beneficio actualizado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando beneficio: '.$e->getMessage());
            return $this->respondError('Error al actualizar beneficio', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = BaBeneficios::findOrFail($id);

            if ($item->postulaciones()->exists() || $item->requisitos()->exists()) {
                return $this->respondError(
                    'No se puede eliminar: tiene postulaciones o requisitos asociados',
                    409
                );
            }

            $item->delete();
            return $this->respond(null, 'Beneficio eliminado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando beneficio: '.$e->getMessage());
            return $this->respondError('Error al eliminar beneficio', 500);
        }
    }
}
