<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $cats = Category::query()
            ->select('id','name','slug','parent_id')
            ->orderBy('parent_id')->orderBy('name')
            ->get();

        if (! $tree) {
            return response()->json($cats);
        }

        // простенька збірка в дерево
        $byParent = $cats->groupBy('parent_id');
        $build = function ($parentId) use (&$build, $byParent) {
            return ($byParent[$parentId] ?? collect())->map(function ($c) use (&$build) {
                return [
                    'id'   => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'children' => $build($c->id),
                ];
            })->values();
        };

        return response()->json($build(null));
    }
}
