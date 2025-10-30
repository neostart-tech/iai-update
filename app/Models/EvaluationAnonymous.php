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
     * Générer un code anonyme unique (2 lettres + 3 chiffres)
     */
    public static function generateUniqueCode(): string
    {
        do {
            // 2 lettres majuscules + 3 chiffres (ex: AB123)
            $letters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2));
            $digits = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $code = $letters . $digits;
        } while (self::where('anonymous_code', $code)->exists());
        
        return $code;
    }
}
