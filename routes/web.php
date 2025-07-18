<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use SolarInvestments\Http\Controllers\ProbeController;

Route::prefix('probes')->name('probes.')->group(function (): void {
    Route::get('/liveness', [ProbeController::class, 'liveness'])->name('liveness');
    Route::get('/readiness', [ProbeController::class, 'readiness'])->name('readiness');

    Route::prefix('liveness')->name('liveness.')->group(function (): void {
        Route::get('/backend', [ProbeController::class, 'livenessBackend'])->name('backend');
        Route::get('/cache', [ProbeController::class, 'livenessCache'])->name('cache');
        Route::get('/database', [ProbeController::class, 'livenessDatabase'])->name('database');
        Route::get('/scheduler', [ProbeController::class, 'livenessScheduler'])->name('scheduler');
        Route::get('/worker', [ProbeController::class, 'livenessWorker'])->name('worker');
    });
});
