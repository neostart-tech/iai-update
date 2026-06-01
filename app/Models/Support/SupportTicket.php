<?php

namespace App\Models\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';
    
    protected $fillable = [
        'reference', 'title', 'description', 'ticketable_type', 'ticketable_id',
        'status', 'priority', 'category_id', 'assigned_to',
        'resolved_at', 'closed_at', 'rating', 'feedback'
    ];
    
    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'rating' => 'integer'
    ];
    
    // Polymorphic relation
    public function ticketable(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'category_id');
    }
    
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at');
    }
    
    // Générer le numéro de ticket
    public function generateReference(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastTicket = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
            
        $number = $lastTicket ? intval(substr($lastTicket->reference, -4)) + 1 : 1;
        
        return sprintf('TKT-%s%s-%04d', $year, $month, $number);
    }
    
    // Vérifier si l'utilisateur peut voir ce ticket
    public function canView(User $user): bool
    {
        // Tout le monde peut voir les tickets
        return true;
    }
}
