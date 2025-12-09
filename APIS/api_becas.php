<?php
use App\Http\Controllers\Api\Becayuda\BaPeriodosController; 
use App\Http\Controllers\Api\Becayuda\BaConvocatoriasController;
use Illuminate\Support\Facades\Route;


//Rutas Becas y Ayudas
Route::middleware('auth:sanctum')->prefix('becayuda')->group(function () {

    // -------- PERIODOS --------
    Route::get('/periodos', [BaPeriodosController::class, 'index']);
    Route::get('/periodos/{periodo}', [BaPeriodosController::class, 'show']);
    Route::post('/periodos', [BaPeriodosController::class, 'store']);
    Route::put('/periodos/{periodo}', [BaPeriodosController::class, 'update']);
    Route::delete('/periodos/{periodo}', [BaPeriodosController::class, 'destroy']);

    

    // -------- CONVOCATORIAS --------
    Route::get('/convocatorias', [BaConvocatoriasController::class, 'index']);
    Route::get('/convocatorias/{convocatoria}', [BaConvocatoriasController::class, 'show']);
    Route::post('/convocatorias', [BaConvocatoriasController::class, 'store']);
    Route::put('/convocatorias/{convocatoria}', [BaConvocatoriasController::class, 'update']);
    Route::delete('/convocatorias/{convocatoria}', [BaConvocatoriasController::class, 'destroy']);
    });

    //-------- BENEFICIOS --------
    Route::get('beneficios',         [BaBeneficiosController::class, 'index']);
    Route::post('beneficios',        [BaBeneficiosController::class, 'store']);
    Route::get('beneficios/{id}',    [BaBeneficiosController::class, 'show']);
    Route::put('beneficios/{id}',    [BaBeneficiosController::class, 'update']);
    Route::delete('beneficios/{id}', [BaBeneficiosController::class, 'destroy']);

    // -------- REQUISITOS --------
    Route::get('/requisitos', [BaRequisitosBeneficiosController::class, 'index']);
    Route::get('/requisitos/{requisito}', [BaRequisitosBeneficiosController::class, 'show']);
    Route::post('/requisitos', [BaRequisitosBeneficiosController::class, 'store']);
    Route::put('/requisitos/{requisito}', [BaRequisitosBeneficiosController::class, 'update']);
    Route::delete('/requisitos/{requisito}', [BaRequisitosBeneficiosController::class, 'destroy']);