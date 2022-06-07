<?php

use App\Http\Controllers\Livewire\QlinkController;
use App\Models\Qlink as ModelsQlink;
use Illuminate\Support\Facades\Route;

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

Route::get('/laravel', function () {
    return view('welcome_laravel');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/view', function () {
    return ModelsQlink::all();
});

Route::get('/qlink/request', [QlinkController::class, 'getLink'])->name('qlink.request');

Route::get('/qlinks', [QlinkController::class, 'show'])->name('qlink.show');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/config', [QlinkController::class, 'configure'])->name('qlink.config');
    Route::get('/qlinks/new', [QlinkController::class, 'create'])->name('qlink.create');
});
