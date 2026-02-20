<?php

namespace App\Services;

use App\Models\{Cours, CoursPresence, Etudiant, StudentUvStatus, UniteValeur, ClassCommitteeMember, EmploiDuTemp, User};
use App\Notifications\Etudiants\EtudiantAbsentNotification;
use App\Notifications\NotificationBase;
use Illuminate\Support\Facades\Notification;

class AttendanceService
{

    public function updateStatusesForEmploi(int $emploiId, string $date): void
    {
        $emploi = EmploiDuTemp::find($emploiId);
        if (!$emploi) return;

        $uvId = $emploi->uv_id;
        $groupId = $emploi->group_id;
        if (!$uvId || !$groupId) return;

        // Compter le nombre total de séances pour cette UV/groupe
        $totalSessions = EmploiDuTemp::where('uv_id', $uvId)
            ->where('group_id', $groupId)
            ->where('type_programme', 'Cours')
            ->count();
            
        if ($totalSessions === 0) return;

        // Récupérer tous les étudiants du groupe qui ont des présences
        $studentIds = CoursPresence::where('emploi_du_temps_id', $emploiId)
            ->where('date', $date)
            ->pluck('etudiant_id')
            ->unique();

        foreach ($studentIds as $sid) {
            // Compter les absences pour cet étudiant dans cette UV/groupe
            $absences = CoursPresence::whereIn('emploi_du_temps_id', function($q) use ($uvId, $groupId) {
                $q->select('id')
                  ->from('emploi_du_temps')
                  ->where('uv_id', $uvId)
                  ->where('group_id', $groupId);
            })
            ->where('etudiant_id', $sid)
            ->where('statut', 'absent')
            ->count();

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

            // Alertes progressives à 10%, 20%, 30%
            $newLevel = 0;
            if ($rate >= 30) {
                $newLevel = 3;
            } elseif ($rate >= 20) {
                $newLevel = 2;
            } elseif ($rate >= 10) {
                $newLevel = 1;
            }
            
            $status->warning_level = $newLevel;
            $status->blocked = $rate >= 30; // Blocage si taux >= 30%
            $status->save();

            if ($newLevel > $previousLevel) {
                $this->notifyThreshold($sid, $uvId, $rate, $newLevel, $status->blocked);
            }
        }
    }

    /**
     * Version simplifiée pour une mise à jour rapide après enregistrement
     */
    public function quickUpdateForStudent(int $etudiantId, int $uvId, int $groupId): void
    {
        // Compter le nombre total de séances pour cette UV/groupe
        $totalSessions = EmploiDuTemp::where('uv_id', $uvId)
            ->where('group_id', $groupId)
            ->where('type_programme', 'Cours')
            ->count();
            
        if ($totalSessions === 0) return;

        // Compter les absences pour cet étudiant
        $absences = CoursPresence::whereIn('emploi_du_temps_id', function($q) use ($uvId, $groupId) {
            $q->select('id')
              ->from('emploi_du_temps')
              ->where('uv_id', $uvId)
              ->where('group_id', $groupId);
        })
        ->where('etudiant_id', $etudiantId)
        ->where('statut', 'absent')
        ->count();

        $rate = $totalSessions > 0 ? round(($absences / $totalSessions) * 100, 2) : 0;

        $status = StudentUvStatus::firstOrNew([
            'etudiant_id' => $etudiantId,
            'uv_id' => $uvId,
            'group_id' => $groupId,
        ]);
        
        $previousLevel = (int)($status->warning_level ?? 0);

        $status->total_sessions = $totalSessions;
        $status->absences_count = $absences;
        $status->absence_rate = $rate;

        $newLevel = 0;
        if ($rate >= 30) {
            $newLevel = 3;
        } elseif ($rate >= 20) {
            $newLevel = 2;
        } elseif ($rate >= 10) {
            $newLevel = 1;
        }
        
        $status->warning_level = $newLevel;
        $status->blocked = $rate >= 30;
        $status->save();

        if ($newLevel > $previousLevel) {
            $this->notifyThreshold($etudiantId, $uvId, $rate, $newLevel, $status->blocked);
        }
    }

    protected function notifyThreshold(int $etudiantId, int $uvId, float $rate, int $level, bool $blocked): void
    {
        // Notification minimale utilisant le canal DB existant
        $title = $blocked ? 'Blocage des évaluations' : 'Avertissement d\'absence';
        $content = $blocked
            ? "Votre taux d'absence a atteint {$rate}% pour cette matière. L'accès aux évaluations est bloqué."
            : "Votre taux d'absence a atteint {$rate}% pour cette matière (niveau d'alerte {$level}).";

        $etudiant = Etudiant::find($etudiantId);
        if (!$etudiant) return;

        // Notification à l'étudiant
        try {
            $etudiant->notify(new EtudiantAbsentNotification($title, $content));
        } catch (\Throwable $e) {
            \Log::error('Erreur notification étudiant: ' . $e->getMessage());
        }

        // Notifier les membres du comité de classe du groupe de l'étudiant
        $groupId = StudentUvStatus::where('etudiant_id', $etudiantId)
            ->where('uv_id', $uvId)
            ->value('group_id');
            
        if ($groupId) {
            $committee = ClassCommitteeMember::where('group_id', $groupId)
                ->where('active', true)
                ->get();
                
            foreach ($committee as $member) {
                $memberUserId = Etudiant::where('id', $member->etudiant_id)->value('user_id');
                if ($memberUserId) {
                    $user = User::find($memberUserId);
                    if ($user) {
                        try { 
                            $user->notify(new NotificationBase(
                                $title, 
                                "{$content} Étudiant: ".$etudiant->completName(), 
                                $level
                            )); 
                        } catch(\Throwable $e) {
                            \Log::error('Erreur notification comité: ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        // Notifier le dernier professeur pour cette UV/groupe
        if (isset($groupId) && $groupId) {
            $emploi = EmploiDuTemp::where('uv_id', $uvId)
                ->where('group_id', $groupId)
                ->latest('debut')
                ->first();
                
            if ($emploi && $emploi->owner_type === User::class && $emploi->owner_id) {
                $prof = User::find($emploi->owner_id);
                if ($prof) {
                    try { 
                        $prof->notify(new NotificationBase(
                            $title, 
                            "{$content} Étudiant: ".$etudiant->completName(), 
                            $level
                        )); 
                    } catch(\Throwable $e) {
                        \Log::error('Erreur notification professeur: ' . $e->getMessage());
                    }
                }
            }
        }
    }
}