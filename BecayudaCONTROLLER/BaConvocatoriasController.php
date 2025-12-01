<?php
namespace App\Http\Controllers\Api\Becayuda;
//namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaPeriodos;
use App\Models\Becayuda\BaConvocatorias;
//use App\Models\Becayuda\BaConvocatorias;
//use App\Models\Becayuda\BaPeriodos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaConvocatoriasController extends Controller
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

    /**
     * GET /api/becayuda/convocatorias
     */
    public function index()
    {
        try {
            $convocatorias = BaConvocatorias::with('periodo')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respond($convocatorias);
        } catch (\Throwable $e) {
            Log::error('Error al obtener convocatorias: ' . $e->getMessage());
            return $this->respondError('Error al obtener convocatorias', 500);
        }
    }

    /**
     * POST /api/becayuda/convocatorias
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_periodo'      => 'required|exists:becayuda.ba_periodos,id',
            'descripcion'     => 'required|string|max:500',
            'fecha_inicio'    => 'required|date',
            'fecha_fin'       => 'required|date|after_or_equal:fecha_inicio',
            'estado'          => 'required|boolean',
            'id_user_created' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $data = $request->all();
            $data['id_user_updated'] = $data['id_user_created'];

            $convocatoria = BaConvocatorias::create($data);

            return $this->respond($convocatoria, 'Convocatoria creada exitosamente', 201);
        } catch (\Throwable $e) {
            Log::error('Error creando convocatoria: ' . $e->getMessage());
            return $this->respondError('Error al crear convocatoria', 500);
        }
    }

    /**
     * GET /api/becayuda/convocatorias/{id}
     */
    public function show($id)
    {
        try {
            $convocatoria = BaConvocatorias::with('periodo')->findOrFail($id);
            return $this->respond($convocatoria);
        } catch (\Throwable $e) {
            return $this->respondError('Convocatoria no encontrada', 404);
        }
    }

    /**
     * PUT /api/becayuda/convocatorias/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_periodo'      => 'sometimes|required|exists:becayuda.ba_periodos,id',
            'descripcion'     => 'sometimes|required|string|max:500',
            'fecha_inicio'    => 'sometimes|required|date',
            'fecha_fin'       => 'sometimes|required|date|after_or_equal:fecha_inicio',
            'estado'          => 'sometimes|required|boolean',
            'id_user_updated' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors()->first(), 422);
        }

        try {
            $convocatoria = BaConvocatorias::findOrFail($id);

            $convocatoria->update($request->all());

            return $this->respond($convocatoria, 'Convocatoria actualizada correctamente');
        } catch (\Throwable $e) {
            Log::error('Error actualizando convocatoria: ' . $e->getMessage());
            return $this->respondError('Error al actualizar convocatoria', 500);
        }
    }

    /**
     * DELETE /api/becayuda/convocatorias/{id}
     */
    public function destroy($id)
    {
        try {
            $convocatoria = BaConvocatorias::findOrFail($id);
            $convocatoria->delete();

            return $this->respond(null, 'Convocatoria eliminada correctamente');
        } catch (\Throwable $e) {
            Log::error('Error eliminando convocatoria: ' . $e->getMessage());
            return $this->respondError('Error al eliminar convocatoria', 500);
        }
    }
}
