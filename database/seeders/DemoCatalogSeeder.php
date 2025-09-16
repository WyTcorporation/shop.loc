<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{

    private const PX = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/6X6x2QAAAAASUVORK5CYII=';

    public function run(): void
    {
        // 5 категорій
        $cats = Category::factory()->count(5)->create();

        // створимо кілька продавців
        $vendors = Vendor::factory()->count(5)->create();

        // 60 товарів, кожному ставимо випадкову категорію та продавця
        $products = Product::factory()->count(60)->make()->each(function (Product $p) use ($cats, $vendors) {
            $p->category_id = $cats->random()->id;
            $p->vendor_id = $vendors->random()->id;
            $p->save();
        });

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
