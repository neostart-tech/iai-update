<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Communication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunicationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Récupérer les IDs des rôles de manière plus robuste pour les modèles polymorphes
        $userRoles = DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->pluck('role_id')
            ->toArray();

        $communications = Communication::with(['author', 'attachments'])
            ->where('is_published', true)
            ->where(function ($query) use ($user, $userRoles) {
                $query->where('target_type', 'all')
                    ->orWhere(function ($q) use ($userRoles) {
                        $q->where('target_type', 'roles');
                        if (!empty($userRoles)) {
                            $q->where(function ($subQ) use ($userRoles) {
                                foreach ($userRoles as $roleId) {
                                    $subQ->orWhereJsonContains('target_data', $roleId)
                                         ->orWhereJsonContains('target_data', (string)$roleId);
                                }
                            });
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target_type', 'specific_users')
                          ->where(function ($subQ) use ($user) {
                              $subQ->whereJsonContains('target_data', $user->id)
                                   ->orWhereJsonContains('target_data', (string)$user->id);
                          });
                    });
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return response()->json($communications);
    }

    public function show(Communication $communication)
    {
        // Vérifier si l'utilisateur peut voir cette communication
        $user = auth()->user();
        if (!$this->canUserSee($user, $communication)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($communication->load(['author', 'attachments']));
    }

    public function markAsRead(Communication $communication)
    {
        $user = auth()->user();
        
        if (!$this->canUserSee($user, $communication)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $user->belongsToMany(Communication::class, 'communication_user')
             ->syncWithoutDetaching([$communication->id => ['read_at' => now()]]);

        return response()->json(['message' => 'Marqué comme lu']);
    }

    public function getUnreadCount()
    {
        $user = auth()->user();
        
        $userRoles = DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->pluck('role_id')
            ->toArray();

        $count = Communication::where('is_published', true)
            ->where(function ($query) use ($user, $userRoles) {
                $query->where('target_type', 'all')
                    ->orWhere(function ($q) use ($userRoles) {
                        $q->where('target_type', 'roles');
                        if (!empty($userRoles)) {
                            $q->where(function ($subQ) use ($userRoles) {
                                foreach ($userRoles as $roleId) {
                                    $subQ->orWhereJsonContains('target_data', $roleId)
                                         ->orWhereJsonContains('target_data', (string)$roleId);
                                }
                            });
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target_type', 'specific_users')
                          ->where(function ($subQ) use ($user) {
                              $subQ->whereJsonContains('target_data', $user->id)
                                   ->orWhereJsonContains('target_data', (string)$user->id);
                          });
                    });
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereDoesntHave('readers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    private function canUserSee($user, $communication)
    {
        if (!$communication->is_published) return false;
        if ($communication->expires_at && $communication->expires_at->isPast()) return false;

        if ($communication->target_type === 'all') return true;

        $targetData = $communication->target_data ?? [];
        if ($communication->target_type === 'roles') {
            $userRoles = DB::table('role_user')
                ->where('user_id', $user->id)
                ->where('user_type', get_class($user))
                ->pluck('role_id')
                ->toArray();
            return !empty(array_intersect($userRoles, $targetData));
        }

        if ($communication->target_type === 'specific_users') {
            return in_array($user->id, $targetData);
        }

        return false;
    }
}
