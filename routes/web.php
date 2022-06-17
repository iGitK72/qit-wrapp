<?php

use App\Http\Controllers\Livewire\QlinkController;
use App\Models\Qlink as ModelsQlink;
use App\Models\QlinkConfiguration;
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
    return view('welcome-laravel');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/jacquemus', [QlinkController::class, 'jacquemus'])->name('demo.jacquemus')->middleware('queue-it');
Route::get('/jacquemus/test', [QlinkController::class, 'jacquemusTest'])->name('demo.jacquemustest')->middleware('queue-it');
Route::get('/jacquemus/protected', [QlinkController::class, 'jacquemusProtected'])->name('demo.jacquemus-protected')->middleware('queue-it');

Route::get('/qlink/request', [QlinkController::class, 'getLink'])->name('qlink.request')->middleware('queue-it');

Route::get('/invite-only', [QlinkController::class, 'inviteOnly'])->name('qlink.inviteo')->middleware('queue-it');
Route::get('/invite-only/guest', [QlinkController::class, 'inviteOnlyGuest'])->name('qlink.inviteoguest')->middleware('queue-it');

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

    Route::get('/qlinks', [QlinkController::class, 'index'])->name('qlink.index');
    Route::get('/qlinks/admin', [QlinkController::class, 'admin'])->name('qlink.admin');
    Route::get('/qlink/{id}/verify', [QlinkController::class, 'verify'])->name('qlink.verify');
    Route::get('/qlink/{id}', [QlinkController::class, 'show'])->name('qlink.show');

    Route::get('/view/config', function () {
        return QlinkConfiguration::all();
    });
    Route::get('/view', function () {
        return ModelsQlink::all();
    });
});

// Auth with Queue-it protection
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'qitauth'
])->group(function () {
    Route::get('/invite-only-with-auth', [QlinkController::class, 'inviteOnlyAuth'])->name('qlink.inviteonly'); // or you can just chain instead of using the qitauth groups, both work fine. ->middleware('queue-it');
    Route::get('/iowr-with-auth', [QlinkController::class, 'inviteOnlyAuth'])->name('qlink.iowr');
});
