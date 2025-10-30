<?php

namespace App\Services;

use App\Models\{Evaluation, EvaluationRoom, Etudiant, Group, Salle};

class EvaluationRoomAllocator
{
    /**
     * Auto-assign rooms and randomly distribute students for an evaluation.
     * - rooms: list of salle IDs to allocate (ordered by preference)
     * - limitPerRoom: optional hard cap per room (<= salle.effectif)
     */
    public function allocate(Evaluation $evaluation, array $roomIds, ?int $limitPerRoom = null): void
    {
        // Collect students of the group and exclude those blocked for this UV/group
        /** @var Group $group */
        $group = $evaluation->group()->with('etudiants')->first();
        $students = $group?->etudiants ?? collect();

        // Determine current UV and Group to evaluate blocking status
        $uvId = (int) ($evaluation->getAttribute('unite_valeur_id') ?? $evaluation->uniteValeur()->getKey());
        $groupId = (int) $evaluation->getAttribute('group_id');

        if ($uvId && $groupId && $students->isNotEmpty()) {
            // Fetch blocked student IDs for this UV and group
            $blockedIds = \App\Models\StudentUvStatus::query()
                ->where('uv_id', $uvId)
                ->where('group_id', $groupId)
                ->where('blocked', true)
                ->pluck('etudiant_id')
                ->all();

            if (!empty($blockedIds)) {
                $students = $students->whereNotIn('id', $blockedIds)->values();
            }
        }

    $ids = $students->pluck('id')->all();
        shuffle($ids);

        // Create EvaluationRoom records
        $rooms = Salle::whereIn('id', $roomIds)->get();
        $slots = [];
        foreach ($rooms as $salle) {
            $capacity = $limitPerRoom ? min($limitPerRoom, (int)$salle->effectif) : (int)$salle->effectif;
            $er = EvaluationRoom::create([
                'evaluation_id' => $evaluation->id,
                'salle_id' => $salle->id,
                'assigned_capacity' => $capacity,
            ]);
            $slots[] = [$er, $capacity];
        }

        // Round-robin fill students into rooms until all students placed or capacities reached
        $i = 0;
        while ($i < count($ids)) {
            $placed = false;
            foreach ($slots as $idx => [$er, $cap]) {
                if ($cap > 0 && $i < count($ids)) {
                    $er->students()->attach($ids[$i]);
                    $slots[$idx][1] = $cap - 1;
                    $i++;
                    $placed = true;
                }
            }
            if (!$placed) break; // capacities exhausted
        }
    }
}
