<?php

namespace Tests\Feature;

use App\Filament\Mine\Resources\Products\Pages\EditProduct;
use App\Filament\Mine\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
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

    public function test_filament_upload_uses_configured_disk(): void
    {
        Storage::fake('s3');
        config(['shop.product_images_disk' => 's3']);

        $product = Product::factory()->create();

        $file = UploadedFile::fake()->image('photo.jpg');

        $translations = collect(config('app.supported_locales'))
            ->filter()
            ->unique()
            ->mapWithKeys(fn (string $locale): array => [
                $locale => $locale === config('app.locale') ? 'Alt' : null,
            ])
            ->all();

        $component = Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ])->mountTableAction('create');

        $component->set('mountedActions.0.data.path', $file);
        $component->set('mountedActions.0.data.disk', ProductImage::defaultDisk());
        $component->set('mountedActions.0.data.alt_translations', $translations);
        $component->set('mountedActions.0.data.alt_translations.uk', 'Alt');
        $component->set('mountedActions.0.data.alt', 'Alt');

        $component->callMountedTableAction()->assertHasNoFormErrors();

        $product->refresh();
        $image = $product->images()->first();

        $this->assertNotNull($image);
        $this->assertSame('s3', $image->disk);
        Storage::disk('s3')->assertExists($image->path);
    }

    public function test_missing_product_directory_is_recreated_on_upload(): void
    {
        Storage::fake('public');
        config(['shop.product_images_disk' => 'public']);

        $product = Product::factory()->create();

        Storage::disk(ProductImage::defaultDisk())->deleteDirectory('products');

        $file = UploadedFile::fake()->image('photo.jpg');

        $translations = collect(config('app.supported_locales'))
            ->filter()
            ->unique()
            ->mapWithKeys(fn (string $locale): array => [
                $locale => $locale === config('app.locale') ? 'Alt' : null,
            ])
            ->all();

        $component = Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ])->mountTableAction('create');

        $component->set('mountedActions.0.data.path', $file);
        $component->set('mountedActions.0.data.disk', ProductImage::defaultDisk());
        $component->set('mountedActions.0.data.alt_translations', $translations);
        $component->set('mountedActions.0.data.alt_translations.uk', 'Alt');
        $component->set('mountedActions.0.data.alt', 'Alt');

        $component->callMountedTableAction()->assertHasNoFormErrors();

        $product->refresh();
        $image = $product->images()->first();

        $this->assertNotNull($image);
        $this->assertSame('public', $image->disk);

        $this->assertTrue(
            Storage::disk('public')->exists("products/{$product->id}")
        );
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_migration_does_not_overwrite_existing_disk_values(): void
    {
        config(['shop.product_images_disk' => 'public']);

        Artisan::call('migrate:rollback', ['--step' => 1, '--force' => true]);

        $product = Product::factory()->create();

        $image = ProductImage::factory()
            ->for($product)
            ->create(['disk' => 's3']);

        Artisan::call('migrate', ['--step' => 1, '--force' => true]);

        $image->refresh();

        $this->assertSame('s3', $image->disk);
    }

    public function test_product_image_urls_respect_custom_disk(): void
    {
        config(['shop.product_images_disk' => 'public']);

        Storage::fake('public');
        Storage::fake('s3');

        $product = Product::factory()->create();

        $path = "products/{$product->id}/cover.jpg";

        Storage::disk('s3')->put($path, 'fake-image');

        $image = ProductImage::factory()
            ->for($product)
            ->create([
                'disk' => 's3',
                'path' => $path,
                'is_primary' => true,
            ]);

        $product->refresh()->load('images');

        $expectedUrl = Storage::disk('s3')->url($image->path);

        $this->assertSame($expectedUrl, $product->preview_url);
        $this->assertSame($expectedUrl, $product->cover_url);
    }
}
