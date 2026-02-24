<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; 

class PlanPaiement extends Model
{
    use HasFactory;

    protected $table="plans_paiement";

     protected $guarded = ["id"];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom);
        });
    }

    public function tranches(): HasMany
    {
        return $this->hasMany(PlanTranche::class);
    }

    public function echeanciers()
    {
        return $this->hasMany(Echeancier::class);
    }

    public function peutSupprimer(): bool
    {
        return $this->echeanciers()->count() === 0;
    }
}
