<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\OrangtuaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\RelasiKelasController;
use App\Http\Controllers\PembayaranSppController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('kelas', KelasController::class);
Route::resource('guru', GuruController::class);
Route::resource('orangtua', OrangtuaController::class);
Route::resource('siswa', SiswaController::class);
Route::resource('relasikelas', RelasiKelasController::class);
Route::resource('pembayaranspp', PembayaranSppController::class);
