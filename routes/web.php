<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\PesertaController;

Route::middleware(['auth', 'prevent-back-history'])->group(function () {

    Route::get('/', function () {
        if (auth()->user()->role === 'operator') {
            return redirect()->route('peserta.kiosk');
        }

        return redirect()->route('dashboard.index');
    });

    Route::get('/kiosk/summary', [PesertaController::class, 'kioskSummary'])->name('peserta.kiosk.summary');
    Route::get('/kiosk', [PesertaController::class, 'kiosk'])->name('peserta.kiosk');
    Route::get('/kiosk/search', [PesertaController::class, 'kioskSearch'])->name('peserta.kiosk.search');
    Route::post('/peserta/{id}/absen', [PesertaController::class, 'absen'])->name('peserta.absen');
    Route::post('/peserta/{id}/pulang', [PesertaController::class, 'pulang'])->name('peserta.pulang');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/peserta', [PesertaController::class, 'index'])->name('peserta.index');
        Route::post('/peserta/import', [PesertaController::class, 'import'])->name('peserta.import');
        Route::post('/peserta/{id}/reset', [PesertaController::class, 'reset'])->name('peserta.reset');

        Route::get('/laporan', [PesertaController::class, 'laporan'])->name('peserta.laporan');
        Route::get('/laporan/export', [PesertaController::class, 'export'])->name('peserta.export');

        Route::resource('/users', UserController::class)->except(['show']);
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    });
});

require __DIR__.'/auth.php';
