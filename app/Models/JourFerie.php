<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class JourFerie extends Model
{
    use HasFactory;

    protected $table = 'jours_feries';

    protected $fillable = [
        'titre',
        'slug',
        'date',
        'est_recurrent',
        'annee_scolaire_id',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'est_recurrent' => 'boolean',
    ];

    /**
     * Boot function for creating slug.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($jourFerie) {
            if (empty($jourFerie->slug)) {
                $jourFerie->slug = Str::slug($jourFerie->titre . '-' . $jourFerie->date);
            }
        });
    }

    /**
     * Get the academic year associated with the holiday.
     */
    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }
}
