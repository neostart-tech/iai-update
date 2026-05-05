<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    protected $fillable = ['libelle', 'ordre', 'code', 'active'];

    protected $casts = [
        'active' => 'boolean',
        'ordre' => 'integer'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('active', function ($builder) {
            $builder->where('active', true);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre', 'asc');
    }

    public function fraisScolarites()
    {
        return $this->hasMany(FraisScolarite::class);
    }

    public function groupes()
    {
        return $this->hasMany(Group::class);
    }

    public function periodes()
    {
        return $this->belongsToMany(Periode::class, 'niveau_periode');
    }
}