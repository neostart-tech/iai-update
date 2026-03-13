<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageReads extends Model
{
    use HasFactory;

    protected $table = 'message_reads';

    protected $fillable = [
        'message_id',
        'reader_id',
        'reader_type',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    public $timestamps = true;

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function reader()
    {
        return $this->morphTo();
    }
}