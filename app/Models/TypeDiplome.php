<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeDiplome extends Model
{
    protected $fillable = ['nom', 'actif', 'ordre'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function champs()
    {
        return $this->hasMany(TypeDiplomeChamp::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'type_diplome_id');
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true)->orderBy('ordre');
    }
}
