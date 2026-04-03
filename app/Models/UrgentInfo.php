<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UrgentInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'summary',
        'file_url',
        'file_path',
        'image',
        'attachments',
        'target_audience',
        'target_group_id',
        'is_published',
        'published_at',
        'created_by',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'target_group_id');
    }

    public function getFullPath(){
        return asset(Storage::url($this->file_path));
    }

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'attachments' => 'array',
    ];
}
