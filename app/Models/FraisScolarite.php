<?php

namespace App\Models;

use App\Enums\GenreEnum;
use App\Enums\ModeFormationEnum;
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
        'mode_formation' => ModeFormationEnum::class,
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
     * Récupérer les frais appropriés selon le genre et le mode de formation de l'étudiant
     */
    public static function getFraisForEtudiant($niveauId, $genre = 'Tous', $filiereId = null, $anneeScolaireId = null, $modeFormation = 'Tous')
    {
        // On récupère l'année passée ou l'année active par défaut
        $anneeTargetId = $anneeScolaireId ?? (\DB::table('annee_scolaires')->where('active', 1)->value('id') ?? 1);

        $query = self::withoutGlobalScopes()
            ->where('annee_scolaire_id', $anneeTargetId)
            ->where('niveau_id', $niveauId);

        // 1. Recherche précise : Filière + Genre + Mode
        $result = (clone $query);
        if ($filiereId) {
            $result->where('filiere_id', $filiereId);
        } else {
            $result->whereNull('filiere_id');
        }
        $res = $result->where('genre', $genre)
                      ->where('mode_formation', $modeFormation)
                      ->first();
        if ($res) return $res;

        // 2. Fallback : Filière + Genre + Mode "Tous"
        $result = (clone $query);
        if ($filiereId) {
            $result->where('filiere_id', $filiereId);
        } else {
            $result->whereNull('filiere_id');
        }
        $res = $result->where('genre', $genre)
                      ->where('mode_formation', 'Tous')
                      ->first();
        if ($res) return $res;

        // 3. Fallback : Filière + Genre "Tous" + Mode spécifique
        $result = (clone $query);
        if ($filiereId) {
            $result->where('filiere_id', $filiereId);
        } else {
            $result->whereNull('filiere_id');
        }
        $res = $result->where('genre', 'Tous')
                      ->where('mode_formation', $modeFormation)
                      ->first();
        if ($res) return $res;

        // 4. Fallback : Filière + Genre "Tous" + Mode "Tous"
        $result = (clone $query);
        if ($filiereId) {
            $result->where('filiere_id', $filiereId);
        } else {
            $result->whereNull('filiere_id');
        }
        $res = $result->where('genre', 'Tous')
                      ->where('mode_formation', 'Tous')
                      ->first();
        if ($res) return $res;

        // 5. Fallback : Niveau + Mode spécifique + Sans filière (on ignore le genre strict)
        $res = self::withoutGlobalScopes()
            ->where('annee_scolaire_id', $anneeTargetId)
            ->where('niveau_id', $niveauId)
            ->whereNull('filiere_id')
            ->where(function($q) {
                $q->where('genre', 'Tous')->orWhereNull('genre');
            })
            ->where('mode_formation', $modeFormation)
            ->first();
        if ($res) return $res;

        // 6. Fallback final sur niveau uniquement
        $res = self::withoutGlobalScopes()
            ->where('annee_scolaire_id', $anneeTargetId)
            ->where('niveau_id', $niveauId)
            ->whereNull('filiere_id')
            ->first();

        return $res;
    }
}
