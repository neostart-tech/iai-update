<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemoaGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'libelle',
        'psp_libelle',
        'methode',
        'currency',
        'logo_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
