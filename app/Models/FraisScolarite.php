<?php

namespace App\Models;

use App\Enums\GenreEnum;
use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Model;

class FraisScolarite extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    protected $guarded = [];

    protected $casts = [
        'genre' => GenreEnum::class,
    ];

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function tranchepaiement()
    {
        return $this->hasMany(TranchePaiement::class);
    }
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    /**
     * Récupérer les frais appropriés selon le genre de l'étudiant
     */
    public static function getFraisForEtudiant($niveauId, $genre, $anneeScolaireId = null)
    {
        $anneeScolaireId = $anneeScolaireId ?? getAnneeScolaireId();

        return self::where('niveau_id', $niveauId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->where(function ($query) use ($genre) {
                $query->where('genre', $genre)
                    ->orWhere('genre', 'Tous');
            })
            ->orderBy('genre', 'desc') // Priorité aux frais spécifiques au genre
            ->first();
    }
}
