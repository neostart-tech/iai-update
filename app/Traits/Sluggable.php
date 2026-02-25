<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    protected static function bootSluggable()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::random(10);
            }
        });
    }
}