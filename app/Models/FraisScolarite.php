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
        'date_debut' => 'date',
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

        // Si une filière spécifique est demandée, on la cherche en priorité
        if ($filiereId) {
            $filiereQuery = (clone $query)->where('filiere_id', $filiereId);

            // 1. Filière + Genre exact + Mode exact
            $res = (clone $filiereQuery)
                ->where('genre', $genre)
                ->where('mode_formation', $modeFormation)
                ->first();
            if ($res) return $res;

            // 2. Filière + Genre 'Tous'/null + Mode exact
            $res = (clone $filiereQuery)
                ->where(function($q) {
                    $q->where('genre', 'Tous')->orWhereNull('genre');
                })
                ->where('mode_formation', $modeFormation)
                ->first();
            if ($res) return $res;

            // 3. Filière + Genre exact + Mode 'Tous'/null
            $res = (clone $filiereQuery)
                ->where('genre', $genre)
                ->where(function($q) {
                    $q->where('mode_formation', 'Tous')->orWhereNull('mode_formation');
                })
                ->first();
            if ($res) return $res;

            // 4. Filière + Genre 'Tous'/null + Mode 'Tous'/null
            $res = (clone $filiereQuery)
                ->where(function($q) {
                    $q->where('genre', 'Tous')->orWhereNull('genre');
                })
                ->where(function($q) {
                    $q->where('mode_formation', 'Tous')->orWhereNull('mode_formation');
                })
                ->first();
            if ($res) return $res;
        }

        // Fallback sans filière (filiere_id IS NULL)
        $globalQuery = (clone $query)->whereNull('filiere_id');

        // 5. Sans filière + Genre exact + Mode exact
        $res = (clone $globalQuery)
            ->where('genre', $genre)
            ->where('mode_formation', $modeFormation)
            ->first();
        if ($res) return $res;

        // 6. Sans filière + Genre 'Tous'/null + Mode exact
        $res = (clone $globalQuery)
            ->where(function($q) {
                $q->where('genre', 'Tous')->orWhereNull('genre');
            })
            ->where('mode_formation', $modeFormation)
            ->first();
        if ($res) return $res;

        // 7. Sans filière + Genre exact + Mode 'Tous'/null
        $res = (clone $globalQuery)
            ->where('genre', $genre)
            ->where(function($q) {
                $q->where('mode_formation', 'Tous')->orWhereNull('mode_formation');
            })
            ->first();
        if ($res) return $res;

        // 8. Sans filière + Genre 'Tous'/null + Mode 'Tous'/null
        $res = (clone $globalQuery)
            ->where(function($q) {
                $q->where('genre', 'Tous')->orWhereNull('genre');
            })
            ->where(function($q) {
                $q->where('mode_formation', 'Tous')->orWhereNull('mode_formation');
            })
            ->first();
        if ($res) return $res;

        // 9. Dernier recours : n'importe quel frais du niveau pour cette année
        return (clone $query)->first();
    }
}

