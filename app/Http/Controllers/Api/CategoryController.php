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

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($tree) {
            $cats = Category::query()
                ->select('id', 'name', 'slug', 'parent_id')
                ->orderBy('parent_id')
                ->orderBy('name')
                ->get();

            if (! $tree) {
                return $cats->toArray();
            }

            // простенька збірка в дерево
            $byParent = $cats->groupBy('parent_id');
            $build = function ($parentId) use (&$build, $byParent) {
                return ($byParent[$parentId] ?? collect())
                    ->map(function ($c) use (&$build) {
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
        });

        return response()->json($data);
    }
}
