<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoyenConnaissance extends Model
{
    protected $table = 'moyens_connaissances';

    protected $fillable = ['libelle', 'actif', 'ordre'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'moyen_connaissance_id');
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true)->orderBy('ordre');
    }
}
