<?php

namespace App\Http\Resources\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Models\{EmploiDuTemp, Evaluation, Group, Scopes\CurrentAnneeScolaireScope, User};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmploiDuTempsResource extends JsonResource
{
	private string $color;

	public function toArray(Request $request): array
	{
		$group = $this->resource->group;
//		dump($this->resource);
		if (!$this->resource->owner)
			dd($this->resource);
		// Ceci n'arrive pas à pointer sur la bonne fiche
//		dd($this->getRightFiche());
		return [
			'slug' => $this->resource->slug,
			'debut' => $this->resource->debut->format('Y-m-d H:i'),
			'date' => $this->resource->debut->format('Y-m-d H:i'),
			'fin' => $this->resource->fin->format('Y-m-d H:i'),
			'details' => $this->resource->details ?? 'Pas de description',
			'type' => $this->resource->type_programme,
			'uv' => $this->resource->uv->nom,
			'uv_id' => $this->resource->uv->slug,
			'salle' => $this->resource->salle->nom,
			'salle_id' => $this->resource->salle->slug,
			'teacher' => $this->resource->owner->nom,
			'teacher_id' => $this->resource->owner->slug,
			'group' => $this->getGroupFullName($group),
			'group_id' => $group->getAttribute('slug'),
			'color' => $this->getColor(),
			'title' => $this->getTitle(),
			'plageHoraire' => $this->getPlageHoraire(),
			'is_controllable' => $isControllable = ($this->isAdministrationMember() && now()->isBetween($this->resource->debut, $this->resource->fin)),
			'control_url' => $isControllable && $this->getRightFiche() ? route('admin.fiches.make', $this->getRightFiche()) : ''
			// Todo Vérifier si on peut vraiment afficher le lien vers la page de contrôle
		];
	}

	private function getColor(): string
	{
		if ($this->resource->type_programme === TypeProgrammeEnum::COURS) return $this->color = 'info';
		elseif ($this->resource->type_programme === TypeProgrammeEnum::EVENEMENT) return $this->color = 'success';
		else return $this->color = 'secondary';
	}

	private function getTitle(): string
{
    if ($this->color === 'info') {
        return 'Cours: ' . $this->resource->uv->nom;
    } elseif ($this->color === 'success') {
        // Vérifiez si la relation evenement existe avant d'y accéder
        return $this->resource->evenement?->name ?? 'Événement sans nom';
    }
    return 'Évaluation: ' . $this->resource->uv->nom;
}

	private function getPlageHoraire(): string
	{
		return $this->resource->debut->translatedFormat('d F Y H:i') . ' à ' . $this->resource->fin->translatedFormat('d F Y H:i');
	}

	private function getGroupFullName(Group $group = null): string
	{
		return $group->getAttribute('nom') . ' - ' . $group->filiere->getAttribute('code');
	}

	private function isAdministrationMember(): bool
	{
		return (boolean)request()->user();
	}

	private function getRightFiche(): object|null
	{
		/**
		 * @var User $user
		 */
		$user = request()->user();
		return $fiche = $user->fiches()->whereMorphRelation('controllable', Evaluation::class, function (Builder $builder) {
			/**
			 * @var EmploiDuTemp $emploiDuTemps
			 */
			$emploiDuTemps = $this->resource;
			return $builder->where('debut', $emploiDuTemps->getAttribute('debut'))
				->where('fin', $emploiDuTemps->getAttribute('fin'))
				->where('salle_id', $emploiDuTemps->getAttribute('salle_id'));
		})->withoutGlobalScope(CurrentAnneeScolaireScope::class)->first();

//		if (!$fiche) return null;

	}
}
