<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
// use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\OrangtuaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\RelasiKelasController;
use App\Http\Controllers\PembayaranSppController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\FotoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


    Route::resource('admin', AdminController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('guru', GuruController::class);
    Route::resource('orangtua', OrangtuaController::class);
    Route::resource('siswa', SiswaController::class);
    Route::resource('relasikelas', RelasiKelasController::class);
    Route::resource('pembayaranspp', PembayaranSppController::class);
    Route::resource('tahunajaran', TahunAjaranController::class);
    Route::resource('agenda', AgendaController::class);
    Route::resource('album', AlbumController::class);
    Route::resource('foto', FotoController::class);
    Route::get('/album/{id}/foto', [FotoController::class, 'getFotoByAlbum']);

