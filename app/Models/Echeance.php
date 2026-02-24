<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echeance extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    use HasFactory;


    protected $guarded = [];

    public function echeancier()
    {
        return $this->belongsTo(Echeancier::class);
    }

    public function paiements()
    {
        return $this->morphMany(Paiement::class, 'payable');
    }
}
