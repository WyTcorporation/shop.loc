<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

//Route::middleware(['auth', 'verified'])->group(function () {
//    Route::get('dashboard', function () {
//        return Inertia::render('dashboard');
//    })->name('dashboard');
//});

//Route::get('/dev/login-admin', function () {
//    $u = User::where('email','admin@admin.com')->firstOrFail();
//    Auth::login($u);
//    session()->put('probe', 'ok');
//    return redirect('/mine');
//});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
