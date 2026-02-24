<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    protected $guarded = ['id'];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }


    public function tranchePaiement()
    {
        return $this->belongsTo(TranchePaiement::class, 'payable_id');
    }

    public function payable()
    {
        return $this->morphTo();
    }
}
