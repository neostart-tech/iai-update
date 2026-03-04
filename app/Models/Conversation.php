<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'conversations';
    
    protected $fillable = [
        'nom', 'type', 'created_by'
    ];

    // Relation avec les participants
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_users', 'conversation_id', 'participant_id')
            ->withPivot('role', 'joined_at')
            ->wherePivot('participant_type', 'App\\Models\\User');
    }

    // Relation avec les messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

      public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    // Relation avec le créateur
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}