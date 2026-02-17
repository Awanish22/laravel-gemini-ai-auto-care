<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PeopleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// Route::resource('/people', PeopleController::class)->except(['show']);

// require __DIR__.'/auth.php';

use App\Http\Controllers\Controller;

Route::get('/', [Controller::class, 'index']);

Route::get('/test-gemini-config', function() {
    return [
        'env_value' => env('GOOGLE_GEMINI_API_KEY') ? 'Found' : 'Not found',
        'config_value' => config('services.gemini.api_key') ? 'Found' : 'Not found',
        'config_file' => config('services.gemini')
    ];
});
Route::get('/debug-env', function() {
    return [
        'env_file_exists' => file_exists(base_path('.env')),
        'env_file_readable' => is_readable(base_path('.env')),
        'gemini_key_from_env' => env('GOOGLE_GEMINI_API_KEY') ? 'Found: ' . substr(env('GOOGLE_GEMINI_API_KEY'), 0, 10) . '...' : 'Not found',
        'gemini_key_from_config' => config('services.gemini.api_key') ? 'Found: ' . substr(config('services.gemini.api_key'), 0, 10) . '...' : 'Not found',
        'all_env_keys' => array_keys($_ENV),
        'services_config' => config('services'),
    ];
});