<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormDataController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteUsersController;
use App\Http\Controllers\SiteChecklistController;
use Illuminate\Support\Facades\Auth;

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
    if (Auth::check()) {
        return redirect()->route('dashboard'); // or just '/dashboard'
    }
    return view('auth.login');
})->name('home');

Route::get('/dashboard',[FormDataController::class,'index'])->name('dashboard');


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

    Route::get('/view/site/checklist', [SiteChecklistController::class, 'index'])->name('site.checklist');

    Route::post('/store/site/checklist', [SiteChecklistController::class, 'store'])->name('sitechecklistdata.store');

    Route::get('employee/log/data',[FormDataController::class, 'list'])->name('employee.log.data');
    Route::get('employee/log/form',[FormDataController::class, 'residentform'])->name('employee.log.form');
    
    Route::post('get-residents', [ResidentController::class, 'getResidents'])->name('get.residents');

    Route::get('form-data-query',[FormDataController::class, 'query']);

});

Route::group(['prefix' => 'admin','middleware' => ['auth', 'admin']], function() {

    Route::get('/dashboard',[FormDataController::class,'index'])->name('admin.dashboard');

    Route::get('/log/data',[FormDataController::class, 'adminlog'])->name('admin.log.data');

    Route::get('/site-resident',[SiteController::class,'index'])->name('admin.resident');

    Route::get('/view/site/checklist', [SiteChecklistController::class, 'indexAdmin'])->name('admin.site.checklist');

    Route::get('/checklist-management',[SiteChecklistController::class,'settings'])->name('admin.checklist.management');
    Route::post('/admin/settings/toggle', [SiteChecklistController::class, 'toggleSetting'])->name('admin.settings.toggle');

     Route::get('users/login/{id}',[SiteUsersController::class,'magicLogin'])->name('users.login');

    Route::resource('sites', SiteController::class);
    Route::resource('residents', ResidentController::class);

    Route::get('/acess/management', [SiteUsersController::class, 'index'])->name('site.access.index');
    Route::post('add/user', [SiteUsersController::class, 'store'])->name('user.store');
    Route::put('user/{id}', [SiteUsersController::class, 'update'])->name('user.update');
    Route::delete('user/{id}', [SiteUsersController::class, 'destroy'])->name('user.destroy');

});

require __DIR__.'/auth.php';
