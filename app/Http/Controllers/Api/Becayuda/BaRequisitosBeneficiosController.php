<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaRequisitosBeneficios;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BaRequisitosBeneficiosController extends Controller
{
    /**
     * GET /api/becayuda/requisitos
     */
    public function index()
    {
        $requisitos = BaRequisitosBeneficios::orderBy('id', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Lista de requisitos obtenida correctamente',
            'data'    => $requisitos,
        ]);
    }

    /**
     * GET /api/becayuda/requisitos/{id}
     */
    public function show($id)
    {
        try {
            $requisito = BaRequisitosBeneficios::findOrFail($id);

            return response()->json([
                'status'  => true,
                'message' => 'Requisito obtenido correctamente',
                'data'    => $requisito,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Requisito no encontrado',
            ], 404);
        }
    }

    /**
     * POST /api/becayuda/requisitos
     */
    public function store(Request $request)
    {
        // Validaciones básicas (sin exists para evitar problemas con el esquema)
        $validated = $request->validate([
            'id_beneficio'          => 'required|integer',
            'id_convocatoria'       => 'required|integer',
            'descripcion_requisito' => 'required|string|max:500',
            'quien_sube_requisito'  => 'required|string|max:100',
            'obligatorio'           => 'required|boolean',
            'estado'                => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $userId = auth()->id();

            $requisito = new BaRequisitosBeneficios($validated);
            $requisito->id_user_created = $userId;
            $requisito->id_user_updated = $userId;
            $requisito->save();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Requisito creado correctamente',
                'data'    => $requisito,
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear requisito: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Error interno al crear el requisito',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/becayuda/requisitos/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $requisito = BaRequisitosBeneficios::findOrFail($id);

            $validated = $request->validate([
                'id_beneficio'          => 'sometimes|required|integer',
                'id_convocatoria'       => 'sometimes|required|integer',
                'descripcion_requisito' => 'sometimes|required|string|max:500',
                'quien_sube_requisito'  => 'sometimes|required|string|max:100',
                'obligatorio'           => 'sometimes|required|boolean',
                'estado'                => 'sometimes|required|boolean',
            ]);

            DB::beginTransaction();

            $requisito->fill($validated);
            $requisito->id_user_updated = auth()->id();
            $requisito->save();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Requisito actualizado correctamente',
                'data'    => $requisito,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Requisito no encontrado',
            ], 404);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar requisito: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Error interno al actualizar el requisito',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/becayuda/requisitos/{id}
     */
    public function destroy($id)
    {
        try {
            $requisito = BaRequisitosBeneficios::findOrFail($id);
            $requisito->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Requisito eliminado correctamente',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Requisito no encontrado',
            ], 404);
        } catch (Throwable $e) {
            Log::error('Error al eliminar requisito: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Error interno al eliminar el requisito',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
