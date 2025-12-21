<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Becayuda\BaPeriodosController; 
use App\Http\Controllers\Api\Becayuda\BaConvocatoriasController; 
use App\Http\Controllers\Api\Becayuda\BaBeneficiosController;
use App\Http\Controllers\Api\Becayuda\BaRequisitosBeneficiosController;
use App\Http\Controllers\Api\Becayuda\BaPostulacionesController;
use App\Http\Controllers\Api\Becayuda\BaAsignacionesController; 
use App\Http\Controllers\Api\Becayuda\BaPagosController; 
use App\Http\Controllers\Api\Becayuda\BaDocumentosPostulacionController;
use App\Http\Controllers\Api\Becayuda\BaComisionBecasController;
use App\Http\Controllers\Api\Becayuda\BaComisionMiembrosController;
use App\Http\Controllers\Api\Becayuda\BaDirectoresController;
use App\Http\Controllers\Api\Becayuda\BaInformesTecnicosController;
use App\Http\Controllers\Api\Becayuda\BaFasesConvocatoriaController;


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
    
        // -------- POSTULACIONES --------
    Route::get('/postulaciones', [BaPostulacionesController::class, 'index']);
    Route::get('/postulaciones/{id}', [BaPostulacionesController::class, 'show']);
    Route::post('/postulaciones', [BaPostulacionesController::class, 'store']);
    Route::put('/postulaciones/{id}', [BaPostulacionesController::class, 'update']);
    Route::delete('/postulaciones/{id}', [BaPostulacionesController::class, 'destroy']);

        // -------- ASIGNACIONES --------
    Route::get('asignaciones', [BaAsignacionesController::class, 'index']);
    Route::get('asignaciones/{id}', [BaAsignacionesController::class, 'show']);
    Route::post('asignaciones', [BaAsignacionesController::class, 'store']);
    Route::put('asignaciones/{id}', [BaAsignacionesController::class, 'update']);
    Route::delete('asignaciones/{id}', [BaAsignacionesController::class, 'destroy']);

        // -------- PAGOS ---------
    Route::get('pagos', [BaPagosController::class, 'index']);
    Route::get('pagos/{id}', [BaPagosController::class, 'show']);
    Route::post('pagos', [BaPagosController::class, 'store']);
    Route::put('pagos/{id}', [BaPagosController::class, 'update']);
    Route::patch('pagos/{id}', [BaPagosController::class, 'update']);
    Route::delete('pagos/{id}', [BaPagosController::class, 'destroy']);

        // -------- DOCUMENTOS --------
    Route::get('documentos-postulacion', [BaDocumentosPostulacionController::class, 'index']);
    Route::get('documentos-postulacion/{id}', [BaDocumentosPostulacionController::class, 'show']);
    Route::get('documentos-postulacion/{id}/download', [BaDocumentosPostulacionController::class, 'download']);
    Route::post('documentos-postulacion', [BaDocumentosPostulacionController::class, 'store']);
    Route::match(['put', 'patch'], 'documentos-postulacion/{documento}', [BaDocumentosPostulacionController::class, 'update']);
    Route::delete('documentos-postulacion/{id}', [BaDocumentosPostulacionController::class, 'destroy']);
