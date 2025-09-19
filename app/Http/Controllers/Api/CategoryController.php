<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
//    public function index(): JsonResponse
//    {
//        return response()->json(
//            Category::query()
//                ->whereNull('parent_id')
//                ->where('is_active', true)
//                ->with(['children' => fn($q) => $q->where('is_active', true)])
//                ->orderBy('name')
//                ->get()
//        );
//    }

    public function index(Request $request): JsonResponse
    {
        $tree = (bool)$request->boolean('tree', false);

        $cacheKey = $tree ? Category::CACHE_KEY_TREE : Category::CACHE_KEY_FLAT;
        $locale = app()->getLocale();

        $cached = Cache::get($cacheKey);

        if (is_array($cached) && array_is_list($cached)) {
            return response()->json($cached);
        }

        if (! is_array($cached)) {
            $cached = [];
        }

        if (! array_key_exists($locale, $cached)) {
            $cached[$locale] = $this->buildCategoriesPayload($tree);
            Cache::put($cacheKey, $cached, now()->addMinutes(10));
        }

        return response()->json($cached[$locale]);
    }

    protected function buildCategoriesPayload(bool $tree): array
    {
        $cats = Category::query()
            ->select('id', 'name', 'name_translations', 'slug', 'parent_id')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        if (! $tree) {
            return $cats
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent_id' => $category->parent_id,
                ])
                ->toArray();
        }

        $byParent = $cats->groupBy('parent_id');

        $build = function ($parentId) use (&$build, $byParent) {
            return ($byParent[$parentId] ?? collect())
                ->map(function (Category $c) use (&$build) {
                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                        'children' => $build($c->id),
                    ];
                })
                ->values();
        };

        return $build(null)->toArray();
    }
}
