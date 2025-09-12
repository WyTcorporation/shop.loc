<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Controllers\SitemapController;

//Route::get('/', function () {
//    return Inertia::render('welcome');
//})->name('home');

Route::get('/sitemap.xml', [SitemapController::class, 'index']);

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Sitemap: ' . url('/sitemap.xml'),
    ];
    return response(implode(PHP_EOL, $lines), 200)
        ->header('Content-Type', 'text/plain');
});

Route::view('{any}', 'shop')->where('any', '^(?!mine|api).*$');

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
