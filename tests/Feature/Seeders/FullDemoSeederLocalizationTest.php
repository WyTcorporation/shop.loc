<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Warehouse;
use Database\Seeders\FullDemoSeeder;
use Illuminate\Support\Facades\Storage;

it('seeds localized content for demo data', function () {
    Storage::fake('public');

    $this->seed(FullDemoSeeder::class);

    $locales = config('app.supported_locales');
    $defaultLocale = config('app.locale');

    $product = Product::with('images')->firstOrFail();
    expect($product->name_translations)->toHaveKeys($locales);
    expect($product->description_translations)->toHaveKeys($locales);

    $image = $product->images->first();
    expect($image)->not->toBeNull();
    expect($image->alt_translations)->toHaveKeys($locales);

    $category = Category::firstOrFail();
    expect($category->name_translations)->toHaveKeys($locales);

    $vendor = Vendor::firstOrFail();
    expect($vendor->name_translations)->toHaveKeys($locales);
    expect($vendor->description_translations)->toHaveKeys($locales);

    $coupon = Coupon::firstOrFail();
    expect($coupon->name_translations)->toHaveKeys($locales);
    expect($coupon->description_translations)->toHaveKeys($locales);

    $warehouse = Warehouse::firstOrFail();
    expect($warehouse->name_translations)->toHaveKeys($locales);
    expect($warehouse->description_translations)->toHaveKeys($locales);

    app()->setLocale('pt');
    Product::query()->searchable();

    $payload = $product->fresh()->toSearchableArray();

    expect($payload['name'])->toBe($product->name_translations[$defaultLocale]);
    expect($payload['name_translations'])->toHaveKeys($locales);
});
