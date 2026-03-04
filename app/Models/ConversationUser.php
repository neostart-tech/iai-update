<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationUser extends Model
{
    use HasFactory;

    protected $table = 'conversation_users';

    protected $fillable = [
        'conversation_id',
        'participant_id',
        'participant_type',
        'role',
        'joined_at'
    ];

    // Désactiver les timestamps car vous n'en avez pas dans la table
    public $timestamps = false;

    // Relation avec la conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Relation polymorphique avec le participant
    public function participant()
    {
        return $this->morphTo();
    }
}