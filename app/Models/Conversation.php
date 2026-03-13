<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'conversations';
    
    protected $fillable = [
        'nom', 'type', 'created_by', 'description', 'admin_only', 
        'last_message_at', 'deleted_at'
    ];
    
    protected $dates = ['deleted_at'];

    // Relation avec les participants de type User
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_users', 'conversation_id', 'participant_id')
            ->withPivot('role', 'joined_at', 'participant_type')
            ->wherePivot('participant_type', 'App\\Models\\User')
            ->orWherePivot('participant_type', 'App\\Models\\User');
    }

    // Relation avec les participants de type Etudiant
    public function participantsEtudiants()
    {
        return $this->belongsToMany(Etudiant::class, 'conversation_users', 'conversation_id', 'participant_id')
            ->withPivot('role', 'joined_at', 'participant_type')
            ->wherePivot('participant_type', 'App\\Models\\Etudiant');
    }

    // Relation avec les participants de type Enseignant (si nécessaire)
    public function participantsEnseignants()
    {
        return $this->belongsToMany(User::class, 'conversation_users', 'conversation_id', 'participant_id')
            ->withPivot('role', 'joined_at', 'participant_type')
            ->wherePivot('participant_type', 'App\\Models\\Enseignant');
    }

    /**
     * Méthode pour obtenir TOUS les participants (Users + Etudiants)
     * avec une seule requête SQL optimisée
     */
    public function getAllParticipants()
    {
        // Récupérer tous les enregistrements de la table pivot pour cette conversation
        $pivotRecords = DB::table('conversation_users')
            ->where('conversation_id', $this->id)
            ->get();
        
        $participants = collect();
        
        foreach ($pivotRecords as $record) {
            if ($record->participant_type === 'App\\Models\\User') {
                $user = User::find($record->participant_id);
                if ($user) {
                    $user->pivot = (object)[
                        'role' => $record->role,
                        'joined_at' => $record->joined_at,
                        'participant_type' => $record->participant_type
                    ];
                    $user->participant_type = 'User';
                    $participants->push($user);
                }
            } 
            elseif ($record->participant_type === 'App\\Models\\Etudiant') {
                $etudiant = Etudiant::find($record->participant_id);
                if ($etudiant) {
                    $etudiant->pivot = (object)[
                        'role' => $record->role,
                        'joined_at' => $record->joined_at,
                        'participant_type' => $record->participant_type
                    ];
                    $etudiant->participant_type = 'Etudiant';
                    $participants->push($etudiant);
                }
            }
        }
        
        // Trier par date d'ajout
        return $participants->sortBy(function($item) {
            return $item->pivot->joined_at ?? null;
        })->values();
    }

    /**
     * Version optimisée avec jointure (si vous voulez une seule requête)
     */
    public function getAllParticipantsOptimized()
    {
        $users = DB::table('conversation_users')
            ->join('users', function($join) {
                $join->on('conversation_users.participant_id', '=', 'users.id')
                     ->where('conversation_users.participant_type', '=', 'App\\Models\\User');
            })
            ->where('conversation_users.conversation_id', $this->id)
            ->select(
                'users.*',
                'conversation_users.role as pivot_role',
                'conversation_users.joined_at as pivot_joined_at',
                'conversation_users.participant_type as pivot_participant_type',
                DB::raw("'User' as participant_type")
            )
            ->get();
        
        $etudiants = DB::table('conversation_users')
            ->join('etudiants', function($join) {
                $join->on('conversation_users.participant_id', '=', 'etudiants.id')
                     ->where('conversation_users.participant_type', '=', 'App\\Models\\Etudiant');
            })
            ->where('conversation_users.conversation_id', $this->id)
            ->select(
                'etudiants.*',
                'conversation_users.role as pivot_role',
                'conversation_users.joined_at as pivot_joined_at',
                'conversation_users.participant_type as pivot_participant_type',
                DB::raw("'Etudiant' as participant_type")
            )
            ->get();
        
        // Formater les résultats
        $users = $users->map(function($user) {
            $user->pivot = (object)[
                'role' => $user->pivot_role,
                'joined_at' => $user->pivot_joined_at,
                'participant_type' => $user->pivot_participant_type
            ];
            unset($user->pivot_role, $user->pivot_joined_at, $user->pivot_participant_type);
            return $user;
        });
        
        $etudiants = $etudiants->map(function($etudiant) {
            $etudiant->pivot = (object)[
                'role' => $etudiant->pivot_role,
                'joined_at' => $etudiant->pivot_joined_at,
                'participant_type' => $etudiant->pivot_participant_type
            ];
            unset($etudiant->pivot_role, $etudiant->pivot_joined_at, $etudiant->pivot_participant_type);
            return $etudiant;
        });
        
        return $users->concat($etudiants)->sortBy('pivot.joined_at')->values();
    }

    // Relation avec les messages
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    // Dernier message de la conversation
    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany();
    }

    // Relation avec le créateur
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope pour les conversations de l'utilisateur connecté
    public function scopeForUser($query, $user)
    {
        return $query->whereHas('participants', function($q) use ($user) {
            $q->where('participant_id', $user->id)
              ->where('participant_type', get_class($user));
        })->orWhereHas('participantsEtudiants', function($q) use ($user) {
            $q->where('participant_id', $user->id)
              ->where('participant_type', get_class($user));
        });
    }
}