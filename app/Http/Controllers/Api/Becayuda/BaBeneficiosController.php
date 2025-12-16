<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaBeneficios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BaBeneficiosController extends Controller
{
    public function index()
    {
        $beneficios = BaBeneficios::orderBy('id', 'asc')->get();

        return response()->json([
            'status' => true,
            'data'   => $beneficios,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'descripcion_sistema'  => 'required|string',
                'descripcion_senescyt' => 'required|string',
                'tipo_beneficio'       => 'required|string|in:beca,ayuda',
                'promedio_minimo'      => 'nullable|numeric',
                'estado'               => 'required|boolean',
            ]);

            $userId = Auth::id() ?? 1; // temporal por si no usas auth aún

            $beneficio = BaBeneficios::create([
                'descripcion_sistema'  => $validated['descripcion_sistema'],
                'descripcion_senescyt' => $validated['descripcion_senescyt'],
                'tipo_beneficio'       => $validated['tipo_beneficio'],
                // Guardamos SOLO el número en requisitos_unicos
                'requisitos_unicos'    => $validated['promedio_minimo'] ?? null,
                'estado'               => $validated['estado'],
                'id_user_created'      => $userId,
                'id_user_updated'      => $userId,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Beneficio creado correctamente',
                'data'    => $beneficio,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Errores de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al crear beneficio: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Error interno al crear el beneficio',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $beneficio = BaBeneficios::findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $beneficio,
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'descripcion_sistema'  => 'sometimes|required|string',
                'descripcion_senescyt' => 'sometimes|required|string',
                'tipo_beneficio'       => 'sometimes|required|string|in:beca,ayuda',
                'promedio_minimo'      => 'nullable|numeric',
                'estado'               => 'sometimes|required|boolean',
            ]);

            $beneficio = BaBeneficios::findOrFail($id);

            if (isset($validated['descripcion_sistema'])) {
                $beneficio->descripcion_sistema = $validated['descripcion_sistema'];
            }
            if (isset($validated['descripcion_senescyt'])) {
                $beneficio->descripcion_senescyt = $validated['descripcion_senescyt'];
            }
            if (isset($validated['tipo_beneficio'])) {
                $beneficio->tipo_beneficio = $validated['tipo_beneficio'];
            }
            if (array_key_exists('promedio_minimo', $validated)) {
                $beneficio->requisitos_unicos = $validated['promedio_minimo'];
            }
            if (isset($validated['estado'])) {
                $beneficio->estado = $validated['estado'];
            }

            $beneficio->id_user_updated = Auth::id() ?? $beneficio->id_user_updated;
            $beneficio->save();

            return response()->json([
                'status'  => true,
                'message' => 'Beneficio actualizado correctamente',
                'data'    => $beneficio,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Errores de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al actualizar beneficio: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Error interno al actualizar el beneficio',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $beneficio = BaBeneficios::findOrFail($id);
        $beneficio->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Beneficio eliminado correctamente',
        ]);
    }
}
