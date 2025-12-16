<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaAsignaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BaAsignacionesController extends Controller
{
    private const ESTADOS_VALIDOS = ['vigente', 'finalizada', 'revocada', 'perdida'];

    public function index(Request $request)
    {
        $query = BaAsignaciones::query()->orderByDesc('id');

        if ($request->filled('estado')) {
            $query->where('estado', mb_strtolower(trim($request->estado)));
        }

        if ($request->filled('id_postulacion')) {
            $query->where('id_postulacion', (int)$request->id_postulacion);
        }

        return response()->json([
            'message' => 'OK',
            'data' => $query->get()
        ], 200);
    }

    public function show($id)
    {
        $asignacion = BaAsignaciones::find($id);

        if (!$asignacion) {
            return response()->json(['message' => 'Asignación no encontrada'], 404);
        }

        return response()->json([
            'message' => 'OK',
            'data' => $asignacion
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            // Normaliza estado a minúscula (así puedes enviar "VIGENTE" y se guarda "vigente")
            if ($request->has('estado')) {
                $request->merge(['estado' => mb_strtolower(trim($request->estado))]);
            }

            $validator = Validator::make($request->all(), [
                'id_postulacion' => [
                    'required',
                    'integer',
                    'exists:pgsql.becayuda.ba_postulaciones,id',
                    // si quieres 1 asignación por postulación, deja esto:
                    Rule::unique('pgsql.becayuda.ba_asignaciones', 'id_postulacion')
                ],
                'monto' => ['required', 'numeric', 'min:0'],
                'porcentaje_sbu' => ['required', 'numeric', 'min:0'],
                'fecha_asignacion' => ['required', 'date'],
                'resolucion' => ['required', 'string', 'max:255'],
                'estado' => ['required', Rule::in(self::ESTADOS_VALIDOS)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            $asignacion = DB::transaction(function () use ($request, $userId) {
                return BaAsignaciones::create([
                    'id_postulacion' => (int) $request->id_postulacion,
                    'monto' => $request->monto,
                    'porcentaje_sbu' => $request->porcentaje_sbu,
                    'fecha_asignacion' => $request->fecha_asignacion,
                    'resolucion' => $request->resolucion,
                    'estado' => $request->estado,
                    'id_user_created' => $userId,
                    'id_user_updated' => null,
                ]);
            });

            return response()->json([
                'message' => 'Asignación creada correctamente',
                'data' => $asignacion
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $asignacion = BaAsignaciones::find($id);
            if (!$asignacion) {
                return response()->json(['message' => 'Asignación no encontrada'], 404);
            }

            if ($request->has('estado')) {
                $request->merge(['estado' => mb_strtolower(trim($request->estado))]);
            }

            $validator = Validator::make($request->all(), [
                'monto' => ['sometimes', 'numeric', 'min:0'],
                'porcentaje_sbu' => ['sometimes', 'numeric', 'min:0'],
                'fecha_asignacion' => ['sometimes', 'date'],
                'resolucion' => ['sometimes', 'string', 'max:255'],
                'estado' => ['sometimes', Rule::in(self::ESTADOS_VALIDOS)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            $asignacion->fill($request->only([
                'monto', 'porcentaje_sbu', 'fecha_asignacion', 'resolucion', 'estado'
            ]));
            $asignacion->id_user_updated = $userId;
            $asignacion->save();

            return response()->json([
                'message' => 'Asignación actualizada correctamente',
                'data' => $asignacion
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $asignacion = BaAsignaciones::find($id);
            if (!$asignacion) {
                return response()->json(['message' => 'Asignación no encontrada'], 404);
            }

            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            // "Eliminar" lógico → en tu caso lo mejor es REVOCAR
            $asignacion->estado = 'revocada';
            $asignacion->id_user_updated = $userId;
            $asignacion->save();

            return response()->json([
                'message' => 'Asignación revocada correctamente',
                'data' => $asignacion
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
