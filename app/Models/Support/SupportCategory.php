<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportCategory extends Model
{
    protected $table = 'support_categories';
    
    protected $fillable = [
        'name', 'slug', 'icon', 'color', 'description', 'is_active', 'order'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'category_id');
    }
}
