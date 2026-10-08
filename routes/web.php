<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormDataController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteUsersController;
use App\Http\Controllers\SiteChecklistController;
use App\Http\Controllers\HealthController;
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

Route::get('/dashboard', [FormDataController::class, 'index'])->name('dashboard')->middleware('auth');

Route::get('users/login/{id}', [SiteUsersController::class, 'magicLogin'])->name('users.login')->middleware('auth');
Route::get('/acess/management', [SiteUsersController::class, 'index'])->name('site.access.index')->middleware('auth');
Route::put('/log/data/{id}', [FormDataController::class, 'updateLogData'])->name('log.update')->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/form/data', [FormDataController::class, 'index'])->name('formdata.index');
    Route::post('/form/data', [FormDataController::class, 'store'])->name('formdata.store');
    // Editing a log goes through log.update (updateLogData); the old formdata.create /
    // formdata.edit / formdata.update routes pointed at missing or broken methods.
    Route::delete('/form/data/{id}', [FormDataController::class, 'destroy'])->name('formdata.destroy');

    Route::get('/view/site/checklist', [SiteChecklistController::class, 'index'])->name('site.checklist');

    Route::post('/store/site/checklist', [SiteChecklistController::class, 'store'])->name('sitechecklistdata.store');

    Route::get('employee/log/data', [FormDataController::class, 'list'])->name('employee.log.data');
    // Server-side CSV export. Scoped identically to the log screens, and covers the whole
    // filtered set rather than just the page on screen.
    Route::get('logs/export', [FormDataController::class, 'export'])->name('logs.export');
    Route::get('employee/log/form', [FormDataController::class, 'residentform'])->name('employee.log.form');

    Route::post('get-residents', [ResidentController::class, 'getResidents'])->name('get.residents');

    Route::get('form-data-query', [FormDataController::class, 'query']);
    Route::get('/checklist-management', [SiteChecklistController::class, 'settings'])->name('admin.checklist.management');
    Route::post('/admin/settings/toggle', [SiteChecklistController::class, 'toggleSetting'])->name('admin.settings.toggle');
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {

    Route::get('/dashboard', [FormDataController::class, 'index'])->name('admin.dashboard');

    Route::get('/log/data', [FormDataController::class, 'adminlog'])->name('admin.log.data');

    Route::get('/site-resident', [SiteController::class, 'index'])->name('admin.resident');
    Route::get('/site-resident/export', [SiteController::class, 'export'])->name('admin.resident.export');

    Route::get('/view/site/checklist', [SiteChecklistController::class, 'indexAdmin'])->name('admin.site.checklist');



    Route::resource('sites', SiteController::class);
    Route::resource('residents', ResidentController::class);

});

/*
 * User management. The URL prefix stays /admin/... because the users view hardcodes these
 * paths, but the middleware is plain "auth": directors, managers and supervisors are meant
 * to manage their own people, and every action below enforces that itself via canManage()
 * and getManageableUserTypes(). Behind the admin middleware these were unreachable for them.
 */
Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {
    Route::post('add/user', [SiteUsersController::class, 'store'])->name('user.store');
    Route::put('user/update/{id}', [SiteUsersController::class, 'update'])->name('user.update');
    Route::delete('user/{id}', [SiteUsersController::class, 'destroy'])->name('user.destroy');

    Route::get('users/export', [SiteUsersController::class, 'export'])->name('user.export');
    Route::post('/user/transfer-sites', [SiteUsersController::class, 'transferSupervisorSites'])->name('user.transfer-sites');
    Route::get('/user/supervisor-sites/{id}', [SiteUsersController::class, 'getSupervisorSites'])->name('user.supervisor-sites');
});

/*
 * Operational health. Admin-only: the checks report server internals such as PHP limits,
 * disk space and record counts.
 */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/health', [HealthController::class, 'index'])->name('health.index');
    Route::get('/health.json', [HealthController::class, 'json'])->name('health.json');
});

require __DIR__ . '/auth.php';
