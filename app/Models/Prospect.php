<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Prospect extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'email',
        'tel',
        'formation_visee',
        'origine',
        'status',
        'slug'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($prospect) {
            if (empty($prospect->slug)) {
                $prospect->slug = Str::uuid()->toString();
            }
        });
    }
}
