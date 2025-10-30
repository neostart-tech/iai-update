<?php

namespace App\Services;

use App\Models\{Cours, CoursPresence, Etudiant, StudentUvStatus, UniteValeur, ClassCommitteeMember, EmploiDuTemp, User};
use App\Notifications\Etudiants\EtudiantAbsentNotification;
use App\Notifications\NotificationBase;
use Illuminate\Support\Facades\Notification;

class AttendanceService
{
    /**
     * Recompute absence stats per UV/group for the given cours and dispatch notifications on threshold crossings.
     */
    public function updateStatusesForCours(Cours $cours): void
    {
        $uvId = $cours->uv_id;
        $groupId = $cours->groupe_id;
        if (!$uvId || !$groupId) return;

        $totalSessions = Cours::where('uv_id', $uvId)->where('groupe_id', $groupId)->count();
        if ($totalSessions === 0) return;

        // For each student having a presence record in this course, recompute
        $studentIds = CoursPresence::where('cours_id', $cours->id)->pluck('etudiant_id')->unique();

        foreach ($studentIds as $sid) {
            $absences = CoursPresence::whereIn('cours_id', function($q) use ($uvId,$groupId){
                $q->select('id')->from('cours')->where('uv_id',$uvId)->where('groupe_id',$groupId);
            })->where('etudiant_id',$sid)->where('statut','absent')->count();

            $rate = $totalSessions > 0 ? round(($absences / $totalSessions) * 100, 2) : 0;

            $status = StudentUvStatus::firstOrNew([
                'etudiant_id' => $sid,
                'uv_id' => $uvId,
                'group_id' => $groupId,
            ]);
            $previousLevel = (int)($status->warning_level ?? 0);

            $status->total_sessions = $totalSessions;
            $status->absences_count = $absences;
            $status->absence_rate = $rate;

            // progressive warnings at 10%, 20%, 30%
            $newLevel = 0;
            if ($rate >= 30) $newLevel = 3; elseif ($rate >= 20) $newLevel = 2; elseif ($rate >= 10) $newLevel = 1; else $newLevel = 0;
            $status->warning_level = $newLevel;
            $status->blocked = $rate >= 30; // beyond threshold, block evaluations
            $status->save();

            if ($newLevel > $previousLevel) {
                $this->notifyThreshold($sid, $uvId, $rate, $newLevel, $status->blocked);
            }
        }
    }

    protected function notifyThreshold(int $etudiantId, int $uvId, float $rate, int $level, bool $blocked): void
    {
        // Minimal notification using existing DB channel
        $title = $blocked ? 'Blocage des évaluations' : 'Avertissement d\'absence';
        $content = $blocked
            ? "Votre taux d'absence a atteint ${rate}% pour cette matière. L'accès aux évaluations est bloqué."
            : "Votre taux d'absence a atteint ${rate}% pour cette matière (niveau d'alerte ${level}).";

        $etudiant = Etudiant::find($etudiantId);
        if (!$etudiant) return;

        // Using Notification facade requires notifiable to be User by default; here we fallback to model if it uses Notifiable
        try {
            $etudiant->notify(new EtudiantAbsentNotification($title, $content));
        } catch (\Throwable $e) {
            // ignore if not notifiable
        }

        // Notify committee members of the student's group for this UV
        $groupId = StudentUvStatus::where('etudiant_id', $etudiantId)->where('uv_id', $uvId)->value('group_id');
        if ($groupId) {
            $committee = ClassCommitteeMember::where('group_id', $groupId)->where('active', true)->get();
            foreach ($committee as $member) {
                $memberUserId = Etudiant::where('id', $member->etudiant_id)->value('user_id');
                if ($memberUserId) {
                    $user = User::find($memberUserId);
                    if ($user) {
                        try { $user->notify(new NotificationBase($title, "${content} Étudiant: ".$etudiant->completName(), $level)); } catch(\Throwable $e) {}
                    }
                }
            }
        }

        // Notify the latest teacher for this UV/group if found
        if (isset($groupId) && $groupId) {
            $emploi = EmploiDuTemp::where('uv_id',$uvId)->where('group_id',$groupId)->latest('debut')->first();
            if ($emploi && $emploi->owner_type === User::class && $emploi->owner_id) {
                $prof = User::find($emploi->owner_id);
                if ($prof) {
                    try { $prof->notify(new NotificationBase($title, "${content} Étudiant: ".$etudiant->completName(), $level)); } catch(\Throwable $e) {}
                }
            }
        }
    }
}
