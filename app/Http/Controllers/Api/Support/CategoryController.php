<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportCategory;
use App\Http\Resources\Support\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = SupportCategory::active()
            ->orderBy('order')
            ->get();
            
        return CategoryResource::collection($categories);
    }
    
    public function show(SupportCategory $category)
    {
        return new CategoryResource($category);
    }
}