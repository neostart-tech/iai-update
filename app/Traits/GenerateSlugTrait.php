<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GenerateSlugTrait
{
    /**
     * Boot the trait
     */
    protected static function bootGenerateSlugTrait()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function ($model) {
           
        });
    }

    /**
     * Generate a unique slug
     */
    public function generateUniqueSlug(): string
    {
        $slug = $this->generateBaseSlug();
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate the base slug
     */
    protected function generateBaseSlug(): string
    {
        return Str::slug(uniqid());
    }

    /**
     * Check if slug exists
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);
        
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }
        
        return $query->exists();
    }

    /**
     * Get route key name for implicit binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}