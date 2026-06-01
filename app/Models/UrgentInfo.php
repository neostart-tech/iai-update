<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;

class UrgentInfo extends Model
{
    use HasFactory, SoftDeletes, GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    protected $fillable = [
        'title',
        'slug',
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
    public function getSlugBaseKeyName(): string
    {
        return 'title';
    }

    /**
     * Permet de résoudre la route par slug ou par ID (pour la transition)
     */
    public function resolveRouteBinding($value, $field = null)
    {
        try {
            return $this->where($field ?? 'slug', $value)
                ->orWhere('id', $value)
                ->first();
        } catch (\Exception $e) {
            // Si la colonne slug n'existe pas encore
            return $this->where('id', $value)->first();
        }
    }

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'attachments' => 'array',
    ];
}
