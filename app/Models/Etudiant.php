<?php

namespace App\Models;

use App\Enums\GenreEnum;
use App\Notifications\Etudiants\PasswordResetLinkSentNotification;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use App\Traits\UserIdentityTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, MorphOne};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

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
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

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

	public function group(): BelongsToMany
	{
		return $this->groups()->latest('annee_scolaire_id');
	}

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


	public function albums(){
		return $this->hasmany(Album::class, 'owner_id', 'id');		
	}

	    public function candidatures()  {
		 return $this->hasMany(Candidature::class);
		 }
public function estAjour() {
    $candidature = $this->candidatures()->latest()->first();
    if (!$candidature) return false;

    $niveau = $candidature->niveau_id;
    $frais = \App\Models\FraisScolarite::where('niveau_id', $niveau)->first();
    if (!$frais) return false;

    $tranches = \App\Models\TranchePaiement::where('frais_scolarite_id', $frais->id)->get();

    foreach ($tranches as $tranche) {
        $totalPaye = \App\Models\Paiement::where('etudiant_id', $this->id)
            ->where('tranche_paiement_id', $tranche->id)
            ->sum('montant');

        if ($totalPaye < $tranche->montant) {
            return false;
        }
    }

    return true;
}

		

		     public function paiements(){ 
				return $this->hasMany(Paiement::class);
			}


	


}
