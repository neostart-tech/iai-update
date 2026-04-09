<?php

namespace App\Models;

use App\Enums\GenreEnum;
use App\Models\Support\SupportTicket;
use App\Notifications\Etudiants\PasswordResetLinkSentNotification;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use App\Traits\UserIdentityTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, MorphMany, MorphOne, MorphToMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static self create(array $attributes)
 * @property Collection<array-key, EmploiDuTemp> $emploiDuTemps
 * @property Collection<array-key, AnnouncementEtudiant> $applications
 * @property Collection<array-key, Note> $notes
 * @property Group $group
 * @property Tuteur $tuteur
 * @property Album $album
 * @property ResponsableFrais $responsableFrais
 */
//#[ScopedBy(CurrentAnneeScolaireScope::class)]
class Etudiant extends Authenticatable
{
	use HasFactory, Notifiable, UserIdentityTrait, CanResetPassword;
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait, HasApiTokens;

	public function hasComplexSlug(): bool
	{
		return true;
	}

	protected $fillable = [
		'nom',
		'nom_jeune_fille',
		'prenom',
		'genre',
		'date_naissance',
		'lieu_naissance',
		'nationalite',
		'tel',
		'email',
		'password',
		'image',
		'annee_admission',
		'matricule',
		'slug',
		'cv',
		'advertiser_id',
		'promotion',
		//		'email_verified_at'
	];



	protected $casts = [
		'genre' => GenreEnum::class,
		'date_naissance' => 'datetime'
	];

	public function notes(): HasMany
	{
		return $this->hasMany(Note::class);
	}

	public function supportTickets()
{
    return $this->morphMany(SupportTicket::class, 'ticketable');
}


	public function roles(): MorphToMany
	{
		return $this->morphToMany(
			Role::class,
			'user',
			'role_user',
			'user_id',
			'role_id'
		);
	}
	//	public function fichesDePresence(): HasMany
	//	{
	//		return $this->hasMany(EmploiDuTemp::class);
	//	}

	public function notesByUv(int $uvId): HasMany
	{
		return $this->notes()->where('ue_id', $uvId);
	}

	public function fichesDePresence(): BelongsToMany
	{
		return $this->belongsToMany(FicheDePresence::class)->using(EtudiantFicheDePresence::class);
	}

	public function ficheDePresenceParEnseignant(int $enseignantId): BelongsToMany
	{
		return $this->fichesDePresence()->where('enseignant_id', $enseignantId);
	}

	public function groups(): BelongsToMany
	{
		return $this->belongsToMany(Group::class, 'etudiant_group')->using(EtudiantGroup::class);
	}

	// 	public function groupes()
	// {
	//     return $this->belongsToMany(Group::class, 'etudiant_group')
	//         ->withPivot(['filiere_id', 'niveau_id', 'annee_scolaire_id'])
	//         ->orderByDesc('etudiant_group.annee_scolaire_id');
	// }

	public function groupes()
	{
		return $this->belongsToMany(Group::class, 'etudiant_group')
			->using(EtudiantGroup::class)
			->wherePivot('annee_scolaire_id', injectAnneeScolaireId())
			->orderByDesc('etudiant_group.id');
	}

	public function etudiantGroups()
	{
		return $this->hasMany(EtudiantGroup::class, 'etudiant_id')
			->where('annee_scolaire_id', injectAnneeScolaireId())
			->latest('id');
	}

	public function tickets()
{
    return $this->morphMany(Ticket::class, 'ticketable');
}
	public function group()
	{
		return $this->hasOneThrough(
			Group::class,
			EtudiantGroup::class,
			'etudiant_id', // Clé étrangère sur etudiant_group
			'id',          // Clé locale sur groups
			'id',          // Clé locale sur etudiants
			'group_id'     // Clé étrangère sur etudiant_group
		)->where('etudiant_group.annee_scolaire_id', injectAnneeScolaireId())
			->latest('etudiant_group.id');
	}



	public function reclamations()
	{
		return $this->hasMany(Reclamation::class);
	}

	public function reclamationsEnCours()
	{
		return $this->reclamations()->where('statut', 'en_attente');
	}

	public function peutReclamer()
	{
		return $this->reclamationsEnCours()->count() < 3;
	}

	// app/Models/Etudiant.php

// Ajoutez ces méthodes à la fin de la classe, avant la dernière accolade

/**
 * Relation avec les frais étudiants (un étudiant peut avoir plusieurs frais sur différentes années)
 */
public function fraisEtudiant()
{
    return $this->hasMany(FraisEtudiant::class, 'etudiant_id');
}

/**
 * Relation avec le frais étudiant de l'année en cours
 */
public function fraisEtudiantActuel()
{
    return $this->hasOne(FraisEtudiant::class, 'etudiant_id')
        ->where('annee_scolaire_id', getAnneeScolaireId());
}




public function echeances()
{
    return $this->hasManyThrough(
        Echeance::class,
        FraisEtudiant::class,
        'etudiant_id', // Clé étrangère sur frais_etudiants
        'frais_etudiant_id', // Clé étrangère sur echeances
        'id', // Clé locale sur etudiants
        'id' // Clé locale sur frais_etudiants
    );
}



	// 	public function filieres()
	// {
	//     return $this->belongsToMany(Filiere::class, 'etudiant_group')
	// 	  ->using(EtudiantGroup::class)
	// 		->wherePivot('annee_scolaire_id', injectAnneeScolaireId())
	//          ->orderByDesc('etudiant_group.id');
	// }



	// public function group(): BelongsToMany
	// {
	// 	return $this->groups()->latest('annee_scolaire_id');
	// }

	public function emploiDuTemps()
	{
		return $this->group()->first()->emploiDuTemps();
	}

	public function tuteur(): MorphOne
	{
		return $this->morphOne(Tuteur::class, 'owner');
	}


	public function album(): MorphOne
	{
		return $this->morphOne(Album::class, 'owner');
	}

	public function responsable(): MorphOne
	{
		return $this->morphOne(ResponsableFrais::class, 'owner');
	}

	public function announcements(): BelongsToMany
	{
		return $this->belongsToMany(Announcement::class)
			->using(AnnouncementEtudiant::class)
			->withPivot('applied')
			->withTimestamps();
	}

	public function announcementEtudiants(): HasMany
	{
		return $this->hasMany(AnnouncementEtudiant::class);
	}

	public function applications(): HasMany
	{
		return $this->announcementEtudiants();
	}

	/**
	 * Retourne true si le tuteur et le responsable des frais sont logiquement les mêmes personnes
	 * @return bool
	 */
	public function areTuteurAndParentTheSamePerson(): bool
	{
		$tuteur = $this->tuteur;
		$responsable = $this->responsableFrais;

		return
			$tuteur->getAttribute('email') === $responsable->getAttribute('email')
			&&
			$tuteur->getAttribute('tel') === $responsable->getAttribute('tel');
	}

	public function sendPasswordResetNotification($token)
	{
		$this->notify(new PasswordResetLinkSentNotification($token, $this->getAttribute('email')));
	}


	public function albums()
	{
		return $this->hasmany(Album::class, 'owner_id', 'id');
	}

	public function candidatures()
	{
		return $this->hasMany(Candidature::class);
	}
	// public function estAjour()
	// {
	// 	$candidature = $this->candidatures()->latest()->first();
	// 	if (!$candidature) return false;

	// 	$niveau = $candidature->niveau_id;
	// 	$frais = FraisScolarite::where('niveau_id', $niveau)->first();
	// 	if (!$frais) return false;

	// 	$tranches = TranchePaiement::where('frais_scolarite_id', $frais->id)->get();

	// 	foreach ($tranches as $tranche) {
	// 		$totalPaye = Paiement::where('etudiant_id', $this->id)
	// 			->where('tranche_paiement_id', $tranche->id)
	// 			->sum('montant');

	// 		if ($totalPaye < $tranche->montant) {
	// 			return false;
	// 		}
	// 	}

	// 	return true;
	// }

	public function estAjour(): bool
	{
		$echeancier = $this->echeancier;

		if (!$echeancier) return false;

		foreach ($echeancier->echeances as $echeance) {
			$totalPaye = $echeance->paiements()->sum('montant');

			if ($totalPaye < $echeance->montant) {
				return false;
			}
		}

		return true;
	}

	public function bourses()
	{
		return $this->belongsToMany(Bourse::class,'bourse_etudiants');
	}



	public function paiements()
	{
		return $this->hasMany(Paiement::class);
	}

	public function gratifications(): HasMany
	{
		return $this->hasMany(Gratification::class);
	}

	/**
	 * Accesseur pour afficher le nom complet en majuscule
	 */
	public function getNomCompletAttribute(): string
	{
		return strtoupper($this->nom) . ' ' . $this->prenom;
	}

	/**
	 * Accesseur pour le nom en majuscule seul
	 */
	public function getNomUpperAttribute(): string
	{
		return strtoupper($this->nom);
	}


	public function clubs()
	{
		return $this->belongsToMany(Club::class, 'club_etudiants')
			->withPivot('date_adhesion')
			->withTimestamps();
	}

	public function ueValidations(): HasMany
	{
		return $this->hasMany(UeValidation::class);
	}


	public function uvValidations(): HasMany
	{
		return $this->hasMany(UvValidation::class);
	}

	public function releveNotes(): HasMany
	{
		return $this->hasMany(ReleveNote::class);
	}

	public function dernierReleve(?int $periodeId = null): ?ReleveNote
	{
		$query = $this->releveNotes()
			->with(['ueValidations.uniteEnseignement', 'uvValidations.uniteValeur'])
			->latest('calcule_le');

		if ($periodeId) {
			$query->where('periode_id', $periodeId);
		}

		return $query->first();
	}



	public function presences(): HasMany
	{
		return $this->hasMany(CoursPresence::class, 'etudiant_id');
	}

	public function advertiser()
	{
		return $this->belongsTo(Advertiser::class);
	}

	/**
	 * Les comportements de l'étudiant
	 */
public function comportements(): HasMany
{
    return $this->hasMany(Comportement::class, 'etudiant_id');
}

/**
 * Les justificatifs de l'étudiant
 */
public function justificatifs(): HasMany
{
    return $this->hasMany(Justificatif::class, 'etudiant_id');
}


/**
 * Statistiques de présence
 */
public function getStatistiquesPresencesAttribute()
{
    $total = $this->presences()->count();
    
    if ($total === 0) {
        return null;
    }
    
    $presents = $this->presences()->where('statut', 'present')->count();
    $absents = $this->presences()->whereIn('statut', ['absent', 'absent_justifie'])->count();
    $retards = $this->presences()->whereIn('statut', ['retard', 'retard_justifie'])->count();
    
    return [
        'total' => $total,
        'presents' => $presents,
        'absents' => $absents,
        'retards' => $retards,
        'taux_presence' => $total > 0 ? round(($presents / $total) * 100, 2) : 0,
        'taux_absent' => $total > 0 ? round(($absents / $total) * 100, 2) : 0,
        'moyenne_retards' => $total > 0 ? round($retards / $total * 100, 2) : 0
    ];
}




	
}
