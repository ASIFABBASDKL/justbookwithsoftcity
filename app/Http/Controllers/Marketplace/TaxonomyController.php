<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Skill;
use App\Support\ApiResponse;

class TaxonomyController extends Controller
{
    public function categories()
    {
        $tree = Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return ApiResponse::success($tree, 'Categories');
    }

    public function skills()
    {
        return ApiResponse::success(Skill::orderBy('name')->get(), 'Skills');
    }
}
