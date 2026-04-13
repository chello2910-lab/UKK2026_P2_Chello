<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
<<<<<<< HEAD
use App\Http\Controllers\TarifController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\LogAktivitasController;
=======
>>>>>>> f474ab34b311fe87a9b8fd39b467fa9d9b20fc34
use App\Http\Controllers\UserController;


Route::get('/', function () {
    return view('auth.login');
});

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

<<<<<<< HEAD
// Untuk User
=======
>>>>>>> f474ab34b311fe87a9b8fd39b467fa9d9b20fc34
Route::get('/user', [UserController::class, 'index'])->name('user');
Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
<<<<<<< HEAD
Route::put('/user/update/{id}', [UserController::class, 'update'])->name('user.update');
Route::delete('/user/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
//untuk tarif
Route::get('/Tarif', [TarifController::class, 'index'])->name('Tarif.tarif');
Route::get('/Tarif/form/{id?}', [TarifController::class, 'form'])->name('Tarif.form');
Route::post('/Tarif/save', [TarifController::class, 'save'])->name('Tarif.save');
Route::post('/Tarif/delete/{id}', [TarifController::class, 'delete'])->name('Tarif.delete');

//untuk area
Route::get('/Area', [AreaController::class, 'area'])->name('Area.area');
Route::get('/Area/create', [AreaController::class, 'create'])->name('Area.form');
Route::get('/Area/{id}/edit', [AreaController::class, 'show'])->name('Area.edit');
Route::post('/Area/store', [AreaController::class, 'store'])->name('Area.store');
Route::delete('/Area/{id}', [AreaController::class, 'destroy'])->name('Area.destroy');

//untuk kendaraan
Route::get('/Kendaraan', [KendaraanController::class, 'kendaraan'])->name('Kendaraan.kendaraan');
Route::get('/Kendaraan/form', [KendaraanController::class, 'create'])->name('Kendaraan.form');
Route::get('/Kendaraan/edit/{id}', [KendaraanController::class, 'show'])->name('Kendaraan.show');
Route::post('/Kendaraan/store', [KendaraanController::class, 'store'])->name('Kendaraan.store');
Route::delete('/Kendaraan/{id}', [KendaraanController::class, 'destroy'])->name('Kendaraan.destroy');

// HALAMAN
Route::get('/transaksi/masuk', [TransaksiController::class, 'masukPage'])->name('Transaksi.masuk');
Route::get('/transaksi/keluar', [TransaksiController::class, 'keluarPage'])->name('Transaksi.keluar');

// AKSI
Route::post('/Transaksi/masuk', [TransaksiController::class, 'masuk']);
Route::post('/Transaksi/keluar/{id}', [TransaksiController::class, 'keluar']);
Route::post('/Transaksi/bayar-cash/{id}', [TransaksiController::class, 'bayarCash']);
Route::get('/struk/{id}', [TransaksiController::class, 'struk']);
// Untuk Riwayat
Route::get('/Riwayat', [RiwayatController::class, 'index'])->name('Riwayat.riwayat');

// Untuk Struk
Route::get('/struk/{id}', [TransaksiController::class, 'struk']);

// Untuk Log Aktivitas
Route::get('/Aktivitas', [LogAktivitasController::class, 'aktivitas'])->name('Aktivitas.aktivitas');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

=======
Route::post('/user/update/{id}', [UserController::class, 'update'])->name('user.update');
Route::post('/user/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
>>>>>>> f474ab34b311fe87a9b8fd39b467fa9d9b20fc34
require __DIR__ . '/auth.php';
