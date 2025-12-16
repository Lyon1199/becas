<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaAsignaciones;
use App\Models\Becayuda\BaPagos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BaPagosController extends Controller
{
    /**
     * IMPORTANTE:
     * Si tu BD tiene CHECK para estado_pago, ajusta esta lista a esos valores.
     * (Puedes sacar el CHECK como hiciste con asignaciones.)
     */
    private const ESTADOS_PAGO = ['pendiente', 'pagado', 'anulado'];

    public function index(Request $request)
    {
        $query = BaPagos::query()->orderByDesc('id');

        if ($request->filled('id_asignacion')) {
            $query->where('id_asignacion', (int)$request->id_asignacion);
        }

        if ($request->filled('estado_pago')) {
            $query->where('estado_pago', mb_strtolower(trim($request->estado_pago)));
        }

        return response()->json([
            'message' => 'OK',
            'data' => $query->get()
        ], 200);
    }

    public function show($id)
    {
        $pago = BaPagos::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        return response()->json([
            'message' => 'OK',
            'data' => $pago
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            if ($request->has('estado_pago')) {
                $request->merge(['estado_pago' => mb_strtolower(trim($request->estado_pago))]);
            }

            $validator = Validator::make($request->all(), [
                'id_asignacion' => ['required', 'integer', 'exists:pgsql.becayuda.ba_asignaciones,id'],
                'valor_pagado'  => ['required', 'numeric', 'min:0.01'],
                'fecha_pago'    => ['required', 'date'],
                'estado_pago'   => ['required', Rule::in(self::ESTADOS_PAGO)],
                'observacion'   => ['nullable', 'string', 'max:500'],
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

            $pago = DB::transaction(function () use ($request, $userId) {

                $asignacion = BaAsignaciones::where('id', $request->id_asignacion)->lockForUpdate()->first();

                if (!$asignacion) {
                    return response()->json(['message' => 'Asignación no encontrada'], 404);
                }

                // Regla recomendada: solo pagar si la asignación está vigente
                if (isset($asignacion->estado) && $asignacion->estado !== 'vigente') {
                    return response()->json([
                        'message' => 'No se puede registrar pago: la asignación no está vigente',
                        'estado_asignacion' => $asignacion->estado
                    ], 409);
                }

                // Suma de pagos NO anulado
                $totalPagado = BaPagos::where('id_asignacion', $asignacion->id)
                    ->where('estado_pago', '!=', 'anulado')
                    ->sum('valor_pagado');

                $restante = (float)$asignacion->monto - (float)$totalPagado;

                if ((float)$request->valor_pagado > $restante) {
                    return response()->json([
                        'message' => 'El pago excede el monto asignado',
                        'monto_asignado' => (float)$asignacion->monto,
                        'total_pagado' => (float)$totalPagado,
                        'restante' => (float)$restante
                    ], 422);
                }

                $nuevoPago = BaPagos::create([
                    'id_asignacion' => (int)$request->id_asignacion,
                    'valor_pagado'  => $request->valor_pagado,
                    'fecha_pago'    => $request->fecha_pago,
                    'estado_pago'   => $request->estado_pago,
                    'observacion'   => $request->observacion,
                    'id_user_created' => $userId,
                ]);

                return $nuevoPago;
            });

            // Si dentro del transaction devolvimos un response (por validación lógica), lo retornamos tal cual
            if ($pago instanceof \Illuminate\Http\JsonResponse) {
                return $pago;
            }

            return response()->json([
                'message' => 'Pago creado correctamente',
                'data' => $pago
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
            $pago = BaPagos::find($id);
            if (!$pago) {
                return response()->json(['message' => 'Pago no encontrado'], 404);
            }

            if ($request->has('estado_pago')) {
                $request->merge(['estado_pago' => mb_strtolower(trim($request->estado_pago))]);
            }

            $validator = Validator::make($request->all(), [
                'valor_pagado' => ['sometimes', 'numeric', 'min:0.01'],
                'fecha_pago'   => ['sometimes', 'date'],
                'estado_pago'  => ['sometimes', Rule::in(self::ESTADOS_PAGO)],
                'observacion'  => ['nullable', 'string', 'max:500'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = DB::transaction(function () use ($request, $pago) {

                // Bloquea asignación y recalcula para no exceder monto si cambia valor_pagado
                $asignacion = BaAsignaciones::where('id', $pago->id_asignacion)->lockForUpdate()->first();

                $nuevoValor = $request->has('valor_pagado') ? (float)$request->valor_pagado : (float)$pago->valor_pagado;
                $nuevoEstado = $request->has('estado_pago') ? $request->estado_pago : $pago->estado_pago;

                // total pagado sin contar este pago (si no está anulado)
                $totalOtros = BaPagos::where('id_asignacion', $asignacion->id)
                    ->where('id', '!=', $pago->id)
                    ->where('estado_pago', '!=', 'anulado')
                    ->sum('valor_pagado');

                // Si el nuevo estado queda "anulado", no afecta suma
                if ($nuevoEstado !== 'anulado') {
                    $restante = (float)$asignacion->monto - (float)$totalOtros;
                    if ($nuevoValor > $restante) {
                        return response()->json([
                            'message' => 'El pago excede el monto asignado',
                            'monto_asignado' => (float)$asignacion->monto,
                            'total_pagado_sin_este' => (float)$totalOtros,
                            'restante' => (float)$restante
                        ], 422);
                    }
                }

                $pago->fill($request->only(['valor_pagado', 'fecha_pago', 'estado_pago', 'observacion']));
                $pago->save();

                return $pago;
            });

            if ($updated instanceof \Illuminate\Http\JsonResponse) {
                return $updated;
            }

            return response()->json([
                'message' => 'Pago actualizado correctamente',
                'data' => $updated
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
            $pago = BaPagos::find($id);

            if (!$pago) {
                return response()->json(['message' => 'Pago no encontrado'], 404);
            }

            // Auditoría: no borrar físico, anular
            $pago->estado_pago = 'anulado';
            $pago->save();

            return response()->json([
                'message' => 'Pago anulado correctamente',
                'data' => $pago
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
