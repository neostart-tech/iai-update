<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportAttachment extends Model
{
    protected $table = 'support_attachments';
    
    protected $fillable = [
        'message_id', 'filename', 'original_name', 'path', 'mime_type', 'size'
    ];
    
    public function message(): BelongsTo
    {
        return $this->belongsTo(SupportMessage::class, 'message_id');
    }
}
