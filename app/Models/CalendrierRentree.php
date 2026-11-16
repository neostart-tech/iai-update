<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CalendrierRentree extends Model
{
    protected $table = 'calendrier_rentrees';

    protected $fillable = [
        'annee_scolaire_id',
        'niveau_id',
        'filiere_id',
        'date_rentree',
        'date_fin',
        'description',
    ];

    protected $casts = [
        'date_rentree' => 'date',
        'date_fin' => 'date',
    ];

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    /**
     * Resout la date de rentree officielle pour une promotion donnee :
     * 1. Date specifique filiere + niveau pour cette annee
     * 2. Date par defaut pour tout le niveau (filiere = null) pour cette annee
     * 3. Date generale de debut de l'annee scolaire
     *
     * @param int $anneeScolaireId
     * @param int $niveauId
     * @param int|null $filiereId
     * @return Carbon|null
     */
    public static function resolveDateRentree($anneeScolaireId, $niveauId, $filiereId = null): ?Carbon
    {
        if (empty($anneeScolaireId) || empty($niveauId)) {
            return null;
        }

        // 1. Exception specifique par filiere & niveau
        if ($filiereId) {
            $specifique = static::where('annee_scolaire_id', $anneeScolaireId)
                ->where('niveau_id', $niveauId)
                ->where('filiere_id', $filiereId)
                ->first();

            if ($specifique && $specifique->date_rentree) {
                return Carbon::parse($specifique->date_rentree);
            }
        }

        // 2. Date generale du niveau pour cette annee
        $niveauDefaut = static::where('annee_scolaire_id', $anneeScolaireId)
            ->where('niveau_id', $niveauId)
            ->whereNull('filiere_id')
            ->first();

        if ($niveauDefaut && $niveauDefaut->date_rentree) {
            return Carbon::parse($niveauDefaut->date_rentree);
        }

        // 3. Date generale de l'annee scolaire
        $annee = AnneeScolaire::find($anneeScolaireId);
        if ($annee && $annee->date_debut) {
            return Carbon::parse($annee->date_debut);
        }

        return null;
    }
}

