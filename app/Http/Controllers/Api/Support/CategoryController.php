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
            
        // Si vide, on essaie de créer les catégories par défaut
        if ($categories->isEmpty()) {
            $defaults = [
                ['name' => 'Technique', 'slug' => 'technique', 'icon' => 'computer', 'color' => '#3b82f6', 'order' => 1],
                ['name' => 'Matériel', 'slug' => 'materiel', 'icon' => 'printer', 'color' => '#10b981', 'order' => 2],
                ['name' => 'Réseau', 'slug' => 'reseau', 'icon' => 'wifi', 'color' => '#8b5cf6', 'order' => 3],
                ['name' => 'Administratif', 'slug' => 'administratif', 'icon' => 'document', 'color' => '#f59e0b', 'order' => 4],
                ['name' => 'Autre', 'slug' => 'autre', 'icon' => 'question', 'color' => '#6b7280', 'order' => 5],
            ];
            
            foreach ($defaults as $def) {
                SupportCategory::firstOrCreate(['slug' => $def['slug']], $def);
            }
            
            $categories = SupportCategory::active()->orderBy('order')->get();
        }
            
        return CategoryResource::collection($categories);
    }
    
    public function show(SupportCategory $category)
    {
        return new CategoryResource($category);
    }
}