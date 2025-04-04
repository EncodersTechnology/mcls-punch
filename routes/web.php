<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormDataController;

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

Route::get('/', function () {
    return view('auth.login');
})->name('home');

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/form/data', [FormDataController::class, 'index'])->name('formdata.index');
    Route::get('/form/data/create', [FormDataController::class, 'create'])->name('formdata.create');
    Route::post('/form/data', [FormDataController::class, 'store'])->name('formdata.store');
    Route::get('/form/data/{id}/edit', [FormDataController::class, 'edit'])->name('formdata.edit');
    Route::put('/form/data/{id}', [FormDataController::class, 'update'])->name('formdata.update');
    Route::delete('/form/data/{id}', [FormDataController::class, 'destroy'])->name('formdata.destroy');

    Route::get('/site/checklist', function () {
        return view('admin.site');
    })->name('site.checklist');
    Route::get('/log/data', function () {
        return view('admin.log');
    })->name('log.data');
    Route::get('/admin/resident', function () {
        return view('admin.resident');
    })->name('admin.resident');
});

require __DIR__.'/auth.php';
