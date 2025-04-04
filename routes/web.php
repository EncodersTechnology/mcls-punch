<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormDataController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SiteController;

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

Route::get('/dashboard',[FormDataController::class,'index'])->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::get('/log/data',[FormDataController::class, 'list'])->name('log.data');
    Route::get('/admin/site-resident',[SiteController::class,'index'])->name('admin.resident');
    
    Route::resource('sites', SiteController::class);
    Route::resource('residents', ResidentController::class);

    Route::post('get-residents', [ResidentController::class, 'getResidents'])->name('get.residents');

    Route::get('form-data-query',[FormDataController::class, 'query']);

});

require __DIR__.'/auth.php';
