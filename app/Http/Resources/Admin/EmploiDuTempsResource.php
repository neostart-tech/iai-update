<?php

namespace App\Http\Resources\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Models\EmploiDuTemp;
use App\Models\Evaluation;
use App\Models\Group;
use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmploiDuTempsResource extends JsonResource
{
    private string $color;

    public function toArray(Request $request): array
    {
        $group = $this->resource->group;
        // Si aucun owner → renvoyer quand même une structure valide
        if (!$this->resource->owner) {
            return [
                'slug' => $this->resource->slug,
                'debut' => $this->resource->debut->format('Y-m-d H:i'),
                'date' => $this->resource->debut->format('Y-m-d H:i'),
                'fin' => $this->resource->fin->format('Y-m-d H:i'),
                'date_debut' => $this->resource->fin->format('Y-m-d '),
                'date_fin' => $this->resource->recurrence_end_date ?? null,
                'heure_debut' => $this->resource->debut->format('H:i'),
                'heure_fin' => $this->resource->fin->format('H:i'),
                'details' => $this->resource->details ?? 'Pas de description',
                'type' => $this->resource->type_programme,
                'uv' => $this->resource->uv?->nom,
                'uv_id' => $this->resource->uv?->slug,
                'salle' => $this->resource->salle?->nom,
                'salle_slug' => $this->resource->salle?->slug,
                'salle_id' => $this->resource->salle?->id,

                // owner null => safe navigation
                'teacher' => null,
                'teacher_id' => null,

                'est_virtuelle' => $this->resource->salle->type === "virtuelle" ? true : false,
                'est_physique' => $this->resource->salle->type === "physique" ? true : false,
   
                // Champs spécifiques aux salles virtuelles
                'lien_reunion' => $this->when($this->resource->salle->type === "virtuelle", $this->salle->lien_reunion),
                'lien_reunion_formate' => $this->when($this->resource->salle->type === "virtuelle", $this->salle->lien_reunion_formate),
                'plateforme' => $this->when($this->resource->salle->type === "virtuelle", $this->salle->plateforme),
                'plateforme_nom' => $this->when($this->resource->salle->type === "virtuelle", $this->salle->plateforme_nom),
                'group' => $group ? $this->getGroupFullName($group) : "--",
                'group_id' => $group?->slug,
                'color' => $this->getColor(),
                'title' => $this->getTitle(),
                'plageHoraire' => $this->getPlageHoraire(),
                'is_controllable' => false,
                'control_url' => '',
                'recurrence_type' => $this->resource->recurrence_type ?? null,
                'recurrence_days' => $this->resource->recurrence_days ?? null,
            ];
        }

        // ----------------------------
        // Cas 2 : owner existe
        // ----------------------------

        return [
            'slug' => $this->resource->slug,
            'debut' => $this->resource->debut->format('Y-m-d H:i'),
            'date' => $this->resource->debut->format('Y-m-d H:i'),
            'fin' => $this->resource->fin->format('Y-m-d H:i'),
            'date_debut' => $this->resource->fin->format('Y-m-d '),
            'date_fin' => $this->resource->recurrence_end_date ?? null,
            'heure_debut' => $this->resource->debut->format('H:i'),
            'heure_fin' => $this->resource->fin->format('H:i'),
            'details' => $this->resource->details ?? 'Pas de description',
            'type' => $this->resource->type_programme,
            'uv' => $this->resource->uv?->nom,
            'uv_id' => $this->resource->uv?->slug,
            'salle' => $this->resource->salle?->nom,
            'salle_slug' => $this->resource->salle?->slug,
            'salle_id' => $this->resource->salle?->id,

            'teacher' => $this->resource->owner?->nom,
            'teacher_id' => $this->resource->owner?->slug,

            'est_virtuelle' => $this->resource->salle->type === "virtuelle" ? true : false,
            'est_physique' => $this->resource->salle->type === "physique" ? true : false,
           

        // Champs spécifiques aux salles virtuelles
                'lien_reunion' => $this->when($this->resource->salle->type === "virtuelle", $this->salle->lien_reunion),
                'lien_reunion_formate' => $this->when($this->resource->salle->type === "virtuelle", $this->salle->lien_reunion_formate),
                'plateforme' => $this->when($this->resource->salle->type === "virtuelle", $this->salle->plateforme),
                'plateforme_nom' => $this->when($this->resource->salle->type === "virtuelle", $this->salle->plateforme_nom),
            'group' => $this->getGroupFullName($group),
            'group_id' => $group?->id,
            'group_slug' => $group?->slug,
            'color' => $this->getColor(),
            'title' => $this->getTitle(),
            'plageHoraire' => $this->getPlageHoraire(),

            'is_controllable' => ($this->isAdministrationMember()
                && now()->isBetween($this->resource->debut, $this->resource->fin)
            ),
            'recurrence_type' => $this->resource->recurrence_type ?? null,
            'recurrence_days' => $this->resource->recurrence_days ?? null,
            // 'control_url' => route('admin.fiches.make', $this->getRightFiche()),
        ];
    }

    private function getColor(): string
    {
        if ($this->resource->type_programme === TypeProgrammeEnum::COURS) {
            return $this->color = 'info';
        } elseif ($this->resource->type_programme === TypeProgrammeEnum::EVENEMENT) {
            return $this->color = 'success';
        } else {
            return $this->color = 'secondary';
        }
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

    private function getGroupFullName(?Group $group = null): string
    {
        return $group->getAttribute('nom') . ' - ' . $group?->niveau?->getAttribute('libelle');
    }

    private function isAdministrationMember(): bool
    {
        return (bool) request()->user();
    }

    private function getRightFiche(): ?object
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
