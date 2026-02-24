<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanTranche extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table="plan_tranches";

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPaiement::class);
    }
}
