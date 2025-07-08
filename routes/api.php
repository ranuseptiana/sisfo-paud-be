<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\CicilanController;
use App\Http\Controllers\OrangtuaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\RelasiKelasController;
use App\Http\Controllers\TahunAjaranController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/test', function(){
    $data = DB::select('select * from "orangtua"');

    try {
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        die("Could not connect to the database.  Please check your configuration. error:" . $e );
    }
//
    return response()->json([
        'message' => 'ok',
        'data' => $data,
    ]);
});

Route::get('/users', [UserController::class, 'index']);
Route::resource('admin', AdminController::class);
Route::resource('kelas', KelasController::class);
Route::resource('guru', GuruController::class);
Route::get('orangtua/export', [OrangtuaController::class, 'exportOrangtua']);
Route::resource('orangtua', OrangtuaController::class);
Route::resource('siswa', SiswaController::class);
// Perbaikan urutan route untuk menghindari konflik
Route::get('pembayaran/export', [PembayaranController::class, 'exportPembayaran']);
Route::get('pembayaran', [PembayaranController::class, 'getPembayaran']);
Route::resource('pembayaran', PembayaranController::class);
// Route::get('pembayaran', [PembayaranController::class, 'getPembayaran']);
Route::resource('relasikelas', RelasiKelasController::class);
//Route::get('pembayaran/export', [PembayaranController::class, 'exportPembayaran']);
//Route::resource('pembayaran', PembayaranController::class);
Route::get('siswa/{idSiswa}/pembayaran/', [PembayaranController::class, 'showByPembayaranIdSiswa']);
Route::resource('cicilan', CicilanController::class);
Route::get('/cicilan/pembayaran/{id}', [CicilanController::class, 'showByPembayaranId']);
Route::resource('tahunajaran', TahunAjaranController::class);
Route::resource('agenda', AgendaController::class);
Route::resource('album', AlbumController::class);
Route::resource('foto', FotoController::class);
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'show']);
Route::middleware('auth:sanctum')->get('/profile', [UserController::class, 'profile']);
Route::get('/album/{id}/foto', [FotoController::class, 'getFotoByAlbum']);
Route::get('siswa/{idSiswa}/kelas', [KelasController::class, 'detailKelas']);
Route::get('guru/{idGuru}/kelas', [KelasController::class, 'daftarKelas']);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('siswa')->group(function(){
        Route::get('/checkBayar/{idSiswa}', [PembayaranController::class, 'showByPembayaranIdSiswa']);
        Route::get('/pembayaran/{idSiswa}/{jenis}', [PembayaranController::class, 'showByJenisPembayaran'])
            ->where('jenis', 'pendaftaran_baru|daftar_ulang');
        Route::get('/export', [SiswaController::class, 'exportSiswa']);
    });
});
