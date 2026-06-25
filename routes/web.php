<?php

use App\Http\Controllers\OAuthController;
use App\Livewire\CreatorDashboard;
use App\Livewire\VotingRoom;
use Illuminate\Support\Facades\Route;

// Landing / Login Page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('login');

// Social Authentication Routes
Route::prefix('auth')->group(function () {
    Route::get('/{provider}', [OAuthController::class, 'redirectToProvider'])
        ->name('oauth.redirect')
        ->where('provider', 'google|github|apple');

    Route::get('/{provider}/callback', [OAuthController::class, 'handleProviderCallback'])
        ->name('oauth.callback')
        ->where('provider', 'google|github|apple');
});

// Authenticated Creator Group
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', CreatorDashboard::class)->name('dashboard');
    Route::post('/logout', [OAuthController::class, 'logout'])->name('logout');
});

// Guest-Friendly Dynamic Planning Poker Room
// NanoID Constraint: Alphanumeric characters, underscores, and hyphens with length of 12-21
Route::get('/room/{id}', VotingRoom::class)
    ->name('room.show')
    ->where('id', '[a-zA-Z0-9_-]{12,21}');

// Locale Toggling Route
Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, ['en', 'es'])) {
        session()->put('locale', $lang);
        cookie()->queue('fibonaughty_locale', $lang, 43200); // 30 days
    }
    return redirect()->back();
})->name('locale.set');

