<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationAnonymous extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'etudiant_id', 
        'anonymous_code',
        'salle_nom'
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    /**
     * Générer un code anonyme unique
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(uniqid(), -8));
        } while (self::where('anonymous_code', $code)->exists());
        
        return $code;
    }
}
