<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaPeriodos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaPeriodosController extends Controller
{
    // Respuesta estándar OK
    private function respond($data = null, $message = '', $status = 200)
    {
        return response()->json([
            'success' => $status >= 200 && $status < 300,
            'data'    => $data,
            'message' => $message,
        ], $status);
    }

    // Respuesta estándar de error
    private function respondError($message, $status = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'data'    => $data,
            'message' => $message,
        ], $status);
    }

    /**
     * GET /api/becayuda/periodos
     */
    public function index()
    {
        try {
            $periodos = BaPeriodos::orderBy('created_at', 'desc')->get();
            return $this->respond($periodos);
        } catch (\Throwable $e) {
            Log::error('Error al obtener periodos: ' . $e->getMessage());
            return $this->respondError('Error al obtener periodos', 500);
        }
    }

    /**
     * POST /api/becayuda/periodos
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion'     => 'required|string|max:500',
            'estado'          => 'required|boolean',
            'id_user_created' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $data = $request->all();

            // Por defecto el que crea también es el que actualiza
            $data['id_user_updated'] = $data['id_user_created'];

            $periodo = BaPeriodos::create($data);

            return $this->respond($periodo, 'Periodo creado exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando periodo: ' . $e->getMessage());
            return $this->respondError('Error al crear periodo', 500);
        }
    }

    /**
     * GET /api/becayuda/periodos/{id}
     */
    public function show($id)
    {
        try {
            $periodo = BaPeriodos::findOrFail($id);
            return $this->respond($periodo);
        } catch (\Throwable $e) {
            return $this->respondError('Periodo no encontrado', 404);
        }
    }

    /**
     * PUT /api/becayuda/periodos/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'descripcion'     => 'sometimes|required|string|max:500',
            'estado'          => 'sometimes|required|boolean',
            'id_user_updated' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $periodo = BaPeriodos::findOrFail($id);

            $data = $request->all();
            $periodo->update($data);

            return $this->respond($periodo, 'Periodo actualizado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando periodo: ' . $e->getMessage());
            return $this->respondError('Error al actualizar periodo', 500);
        }
    }

    /**
     * DELETE /api/becayuda/periodos/{id}
     */
    public function destroy($id)
    {
        try {
            $periodo = BaPeriodos::findOrFail($id);

            // No permitir borrar si tiene convocatorias asociadas
            if ($periodo->convocatorias()->exists()) {
                return $this->respondError(
                    'No se puede eliminar: tiene convocatorias asociadas',
                    409
                );
            }

            $periodo->delete();

            return $this->respond(null, 'Periodo eliminado correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando periodo: ' . $e->getMessage());
            return $this->respondError('Error al eliminar periodo', 500);
        }
    }
}
