<?php

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('products', function (Request $r) {
    $q = $r->get('search');
    $builder = $q ? Product::search($q) : Product::query();
    if (!$q) $builder = $builder->where('is_active', true)->latest();
    return $builder->paginate(12);
});

Route::get('products/{slug}', fn($slug) => Product::where('slug', $slug)->with('images', 'category')->firstOrFail());
