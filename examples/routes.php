<?php

use App\Http\Controllers\BoletoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Banco do Brasil API Routes
|--------------------------------------------------------------------------
|
| Here are the route definitions for the Banco do Brasil integration.
| These routes provide a RESTful API for managing boletos.
|
*/

Route::prefix('api/bb')->group(function () {
    
    // Boleto routes
    Route::prefix('boletos')->group(function () {
        Route::get('/', [BoletoController::class, 'index'])->name('bb.boletos.index');
        Route::post('/', [BoletoController::class, 'store'])->name('bb.boletos.store');
        Route::get('/{id}', [BoletoController::class, 'show'])->name('bb.boletos.show');
        Route::patch('/{id}', [BoletoController::class, 'update'])->name('bb.boletos.update');
        Route::delete('/{id}', [BoletoController::class, 'baixar'])->name('bb.boletos.baixar');
        
        // PIX operations
        Route::post('/{id}/pix', [BoletoController::class, 'gerarPix'])->name('bb.boletos.gerar-pix');
        Route::delete('/{id}/pix', [BoletoController::class, 'cancelarPix'])->name('bb.boletos.cancelar-pix');
        Route::get('/{id}/pix', [BoletoController::class, 'consultarPix'])->name('bb.boletos.consultar-pix');
    });
    
    // Webhook route (should be accessible without authentication)
    Route::post('webhook/baixa-operacional', [BoletoController::class, 'webhookBaixaOperacional'])
        ->name('bb.webhook.baixa-operacional')
        ->withoutMiddleware(['auth', 'verified']); // Remove authentication if present
});

/*
|--------------------------------------------------------------------------
| Example usage in your application
|--------------------------------------------------------------------------
|
| GET /api/bb/boletos - List all boletos with optional filters
| POST /api/bb/boletos - Create a new boleto
| GET /api/bb/boletos/{id} - Get a specific boleto
| PATCH /api/bb/boletos/{id} - Update a boleto
| DELETE /api/bb/boletos/{id} - Cancel a boleto (baixar)
| POST /api/bb/boletos/{id}/pix - Generate PIX for boleto
| DELETE /api/bb/boletos/{id}/pix - Cancel PIX for boleto
| GET /api/bb/boletos/{id}/pix - Get PIX details for boleto
| POST /api/bb/webhook/baixa-operacional - Webhook endpoint for payment notifications
|
*/