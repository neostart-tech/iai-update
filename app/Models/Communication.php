<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Communication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'content',
        'type',
        'target_type',
        'target_data',
        'is_published',
        'published_at',
        'expires_at',
        'author_id',
        'slug'
    ];

    protected $casts = [
        'target_data' => 'json',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->slug = Str::slug($model->title) . '-' . substr($model->uuid, 0, 8);
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommunicationAttachment::class);
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'communication_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
