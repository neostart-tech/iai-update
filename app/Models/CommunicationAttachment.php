<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CommunicationAttachment extends Model
{
    protected $fillable = [
        'communication_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size'
    ];

    protected $appends = ['file_url'];

    public function communication(): BelongsTo
    {
        return $this->belongsTo(Communication::class);
    }

    public function getFileUrlAttribute(): string
    {
        return asset(Storage::url($this->file_path));
    }
}
