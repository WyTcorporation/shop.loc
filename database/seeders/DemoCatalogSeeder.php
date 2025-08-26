<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory()->count(5)->create()->each(function ($cat) {
            Product::factory()->count(12)->create([
                'category_id' => $cat->id,
            ])->each(function ($p) {
                $p->images()->createMany(
                    collect(range(1, mt_rand(1, 3)))->map(fn () => [
                        'path' => 'placeholders/'.Str::uuid().'.jpg',
                    ])->all()
                );
            });
        });
    }
}
