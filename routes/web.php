<?php

use App\Http\Controllers\LinkController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CutURL routes
|--------------------------------------------------------------------------
|
| Application routes are declared first so they always take precedence over
| the catch-all redirect route, which must stay last.
|
*/

Route::get('/', [LinkController::class, 'home'])->name('home');

Route::post('/shorten', [LinkController::class, 'shorten'])
    ->middleware('throttle:shorten')
    ->name('shorten');

Route::get('/dashboard', [LinkController::class, 'dashboard'])->name('dashboard');

Route::delete('/links/{id}', [LinkController::class, 'destroy'])->name('links.destroy');
Route::delete('/links', [LinkController::class, 'clear'])->name('links.clear');

/*
| Catch-all redirect route. Constrained to safe short-code characters so it
| never swallows requests for static assets or malformed paths. Keep this
| declaration LAST.
*/
Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9_-]+')
    ->name('redirect');
