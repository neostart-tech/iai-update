<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeDiplomeChamp extends Model
{
    // Ensemble fixe de clés valides pour ce modèle — un champ du parcours scolaire
    // n'apparaît pour un type de diplôme donné que s'il a une ligne ici.
    public const CHAMPS_DISPONIBLES = [
        'mention_bac' => 'Mention au BAC',
        'serie' => 'Série du BAC',
        'numero_table' => 'Numéro de table',
        'etablissement_diplome' => 'Dernier établissement fréquenté',
        'annee_bac' => "Année d'obtention",
    ];

    protected $fillable = ['type_diplome_id', 'champ_key', 'obligatoire'];

    protected $casts = [
        'obligatoire' => 'boolean',
    ];

    public function typeDiplome()
    {
        return $this->belongsTo(TypeDiplome::class);
    }
}
