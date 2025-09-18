<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{

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

        // 5 категорій
        $cats = Category::factory()->count(5)->create();

        // створимо кілька продавців
        $vendors = Vendor::factory()->count(5)->create();

        // 60 товарів, кожному ставимо випадкову категорію та продавця
        $products = Product::factory()
            ->count(60)
            ->state(function () use ($cats, $vendors) {
                return [
                    'category_id' => $cats->random()->id,
                    'vendor_id' => $vendors->random()->id,
                ];
            })
            ->create();

        // 1–3 зображення на товар + одне головне
        $products->each(function (Product $p) {
            $count = random_int(1, 3);

            foreach (range(1, $count) as $i) {
                $path = "products/{$p->id}/seed-{$i}.png";
                Storage::disk('public')->put($path, base64_decode(self::PX));

                $p->images()->create([
                    'path'       => $path,
                    'disk'       => 'public',
                    'alt'        => "{$p->name} #{$i}",
                    'sort'       => $i - 1,
                    'is_primary' => $i === 1, // перше — головне
                ]);
            }
        });

        Product::query()->searchable();
    }

//    /**
//     * Run the database seeds.
//     */
//    public function run(): void
//    {
//        Category::factory()->count(5)->create()->each(function ($cat) {
//            Product::factory()->count(12)->create([
//                'category_id' => $cat->id,
//            ])->each(function ($p) {
//                $p->images()->createMany(
//                    collect(range(1, mt_rand(1, 3)))->map(fn () => [
//                        'disk' => 'public',
//                        'path' => 'placeholders/'.Str::uuid().'.jpg',
//                    ])->all()
//                );
//            });
//        });
//    }
}
