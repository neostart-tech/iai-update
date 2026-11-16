<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EtudiantProfileRequest extends Model
{
    use HasFactory;

    protected $table = 'etudiant_profile_requests';

    protected $fillable = [
        'etudiant_id',
        'data',
        'documents',
        'statut',
        'motif_refus',
        'traite_par',
        'traite_le',
    ];

    protected $casts = [
        'data' => 'array',
        'documents' => 'array',
        'traite_le' => 'datetime',
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }
}
