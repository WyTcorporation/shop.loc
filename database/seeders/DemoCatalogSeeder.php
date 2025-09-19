<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Database\Seeders\Concerns\GeneratesLocalizedText;
use Database\Support\TranslationGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    use GeneratesLocalizedText;
    private const PX = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/6X6x2QAAAAASUVORK5CYII=';

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        ProductImage::withoutEvents(fn () => ProductImage::truncate());
        Product::withoutEvents(fn () => Product::truncate());
        Vendor::withoutEvents(fn () => Vendor::truncate());
        Category::withoutEvents(fn () => Category::truncate());

        Schema::enableForeignKeyConstraints();

        Product::query()->unsearchable();

        $themes = TranslationGenerator::themes();

        $categories = collect($themes)->mapWithKeys(function (string $theme) {
            $localized = $this->localized(TranslationGenerator::categoryName($theme));

            $category = Category::factory()->create([
                'name' => $localized['value'],
                'name_translations' => $localized['translations'],
                'slug' => Str::slug($localized['value']) . '-' . Str::lower(Str::random(6)),
            ]);

            return [$theme => $category];
        });

        $vendors = $categories->map(function (Category $_, string $theme) {
            $vendorSet = TranslationGenerator::vendorSet($theme);
            $name = $this->localized($vendorSet['name']);
            $description = $this->localized($vendorSet['description']);

            $vendor = Vendor::factory()->create([
                'name' => $name['value'],
                'name_translations' => $name['translations'],
                'slug' => Str::slug($name['value']) . '-' . Str::lower(Str::random(5)),
                'description' => $description['value'],
                'description_translations' => $description['translations'],
            ]);

            return [
                'vendor' => $vendor,
                'brand' => $vendorSet['brand'],
            ];
        });

        $productData = collect(range(1, 60))->map(function () use ($categories, $vendors) {
            $theme = $categories->keys()->random();
            $category = $categories->get($theme);
            $vendorInfo = $vendors->get($theme);

            $productSet = TranslationGenerator::productSet($theme, $vendorInfo['brand']);
            $name = $this->localized($productSet['name']);

            $product = Product::factory()->create([
                'category_id' => $category->id,
                'vendor_id' => $vendorInfo['vendor']->id,
                'name' => $name['value'],
                'name_translations' => $name['translations'],
                'slug' => Str::slug($name['value']) . '-' . Str::lower(Str::random(6)),
            ]);

            return [
                'product' => $product,
                'theme' => $theme,
                'name_translations' => $name['translations'],
            ];
        });

        $productData->each(function (array $item) {
            /** @var Product $product */
            $product = $item['product'];
            $count = random_int(1, 3);

            foreach (range(1, $count) as $i) {
                $path = "products/{$product->id}/seed-{$i}.png";
                Storage::disk('public')->put($path, base64_decode(self::PX));

                $alt = TranslationGenerator::imageAlt($item['theme'], $item['name_translations'], $i);
                $altLocalized = $this->localized($alt);

                $product->images()->create([
                    'path'       => $path,
                    'disk'       => 'public',
                    'alt'        => $altLocalized['value'],
                    'alt_translations' => $altLocalized['translations'],
                    'sort'       => $i - 1,
                    'is_primary' => $i === 1,
                ]);
            }
        });

        app()->setLocale(config('app.locale'));
        Product::query()->searchable();
    }
}
