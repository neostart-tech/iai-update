<?php

namespace App\Models;

use App\Enums\GenreEnum;
use App\Http\Resources\AnnonceResource;
use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([CurrentAnneeScolaireScope::class])]
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

    public function getMontantTotalAttribute()
{
    return $this->tranchepaiement()->sum('montant');
}
    /**
     * Récupérer les frais appropriés selon le genre de l'étudiant
     */
    public static function getFraisForEtudiant($niveauId, $genre = 'Tous', $filiereId = null, $anneeScolaireId = null)
    {
        // On récupère l'année passée ou l'année active par défaut
        $anneeTargetId = $anneeScolaireId ?? (\DB::table('annee_scolaires')->where('active', 1)->value('id') ?? 1);

        $query = self::withoutGlobalScopes()
            ->where('annee_scolaire_id', $anneeTargetId)
            ->where('niveau_id', $niveauId);

        // 1. Recherche précise : Filière + Genre
        $result = (clone $query);
        if ($filiereId) {
            $result->where('filiere_id', $filiereId);
        } else {
            $result->whereNull('filiere_id');
        }
        $res = $result->where('genre', $genre)->first();
        if ($res) return $res;

        // 2. Fallback : Filière + Genre "Tous"
        $result = (clone $query);
        if ($filiereId) {
            $result->where('filiere_id', $filiereId);
        } else {
            $result->whereNull('filiere_id');
        }
        $res = $result->where('genre', 'Tous')->first();
        if ($res) return $res;

        // 3. Fallback : Pas de filière + Genre "Tous" ou spécifique ou NULL
        $res = self::withoutGlobalScopes()
            ->where('annee_scolaire_id', $anneeTargetId)
            ->where('niveau_id', $niveauId)
            ->whereNull('filiere_id')
            ->where(function($q) use ($genre) {
                $q->whereIn('genre', [$genre, 'Tous'])
                  ->orWhereNull('genre');
            })
            ->first();

        return $res;
    }
}
