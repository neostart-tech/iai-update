<?php
// app/Models/Reclamation.php

namespace App\Models;

use App\Traits\GenerateSlugTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reclamation extends Model
{
    use SoftDeletes, GenerateSlugTrait;

    protected $fillable = [
        'etudiant_id',
        'evaluation_id',
        'note_id',
        'motif',
        'fichier_justificatif',
        'statut',
        'nouvelle_note',
        'commentaire_admin',
        'traitee_par',
        'traitee_le'
    ];

    protected $casts = [
        'traitee_le' => 'datetime',
        'nouvelle_note' => 'decimal:2'
    ];

    protected function generateBaseSlug(): string
    {
        $timestamp = now()->format('YmdHis');
        return "recla-{$this->etudiant_id}-{$this->evaluation_id}-{$timestamp}";
    }
    // Relations
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    public function traiteePar()
    {
        return $this->belongsTo(User::class, 'traitee_par');
    }

    public function historiqueNotes()
    {
        return $this->hasMany(NoteHistorique::class);
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopePourEtudiant($query, $etudiantId)
    {
        return $query->where('etudiant_id', $etudiantId);
    }

    // Méthodes utilitaires
    public function estModifiable()
    {
        return $this->statut === 'en_attente';
    }

    public function estApprouvee()
    {
        return $this->statut === 'approuvee';
    }
}
