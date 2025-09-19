<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function created(Product $product): void
    {
        if ($product->shouldBeSearchable()) {
            $product->searchable();
        }
    }

    public function updated(Product $product): void
    {
        if ($product->wasChanged(['name','name_translations','slug','sku','category_id','price','stock','is_active','attributes'])) {
            $product->shouldBeSearchable()
                ? $product->searchable()
                : $product->unsearchable();
        }
    }

    public function deleted(Product $product): void
    {
        $product->unsearchable();
    }

    public function restored(Product $product): void
    {
        if ($product->shouldBeSearchable()) {
            $product->searchable();
        }
    }

    public function forceDeleted(Product $product): void
    {
        $product->unsearchable();
    }
}
