<?php

use App\Http\Controllers\ProfileController;
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

Route::middleware(['auth'])->group(function () {

    Route::get('/', [PesertaController::class, 'index'])->name('peserta.index');

    Route::get('/peserta', [PesertaController::class, 'index']);

    Route::post('/peserta/import', [PesertaController::class, 'import'])->name('peserta.import');

    Route::post('/peserta/{id}/absen', [PesertaController::class, 'absen'])->name('peserta.absen');

    Route::post('/peserta/{id}/reset', [PesertaController::class, 'reset'])->name('peserta.reset');

    Route::get('/laporan', [PesertaController::class, 'laporan'])->name('peserta.laporan');

    Route::get('/laporan/export', [PesertaController::class, 'export'])->name('peserta.export');

    Route::get('/kiosk', [PesertaController::class, 'kiosk'])->name('peserta.kiosk');

    Route::get('/kiosk/search', [PesertaController::class, 'kioskSearch'])->name('peserta.kiosk.search');

});

require __DIR__.'/auth.php';
