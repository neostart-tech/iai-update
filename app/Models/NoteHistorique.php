<?php

namespace App\Models;

use App\Traits\GenerateSlugTrait;
use Illuminate\Database\Eloquent\Model;

class NoteHistorique extends Model
{
    use GenerateSlugTrait;

    protected $table = 'notes_historique';

    protected $fillable = [
        'note_id',
        'reclamation_id',
        'ancienne_note',
        'nouvelle_note',
        'modifiee_par'
    ];

    protected $casts = [
        'ancienne_note' => 'decimal:2',
        'nouvelle_note' => 'decimal:2'
    ];

    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    public function reclamation()
    {
        return $this->belongsTo(Reclamation::class);
    }

    public function modifieePar()
    {
        return $this->belongsTo(User::class, 'modifiee_par');
    }

    protected function generateBaseSlug(): string
    {
        $timestamp = now()->format('YmdHis');
        return "hist-{$this->note_id}-{$this->reclamation_id}-{$timestamp}";
    }
}
