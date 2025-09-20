<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageDiskTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_image_defaults_to_public_disk_and_cleans_up_files(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();

        $file = UploadedFile::fake()->image('photo.jpg');
        $path = $file->store("products/{$product->id}", 'public');

        $image = new ProductImage([
            'product_id' => $product->id,
            'path' => $path,
        ]);

        $image->save();

        $this->assertSame('public', $image->disk);
        Storage::disk('public')->assertExists($path);

        $image->delete();

        Storage::disk('public')->assertMissing($path);
    }
}
