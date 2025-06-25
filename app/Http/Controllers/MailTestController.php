<?php

// Adicionar estas rotas no seu routes/web.php

use App\Http\Controllers\MailTestController;

// Rotas de teste de email (apenas para admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/test-email', [MailTestController::class, 'index'])->name('test-email');
    Route::post('/test-email/connection', [MailTestController::class, 'testConnection'])->name('test-connection');
    Route::post('/test-email/send', [MailTestController::class, 'sendTest'])->name('test-send');
    Route::post('/test-email/send-appointment', [MailTestController::class, 'sendAppointmentTest'])->name('test-appointment');
    Route::post('/test-email/logs', [MailTestController::class, 'viewLogs'])->name('test-logs');
});