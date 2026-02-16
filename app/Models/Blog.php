<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	public function getSlugBaseKeyName(): string
	{
		return 'title';
	}

	public function hasComplexSlug(): bool
	{
		return true;
	}

	protected $fillable = [
		'title',
		'author_name',
		'image',
		'content',
		'status',
		'publication_date'
	];

	public $timestamps = false;

	protected $casts = [
		'publication_date' => 'datetime'
	];

	public function getFullPath(){
		return asset(Storage::url($this->image));
	}
	public function comments(): HasMany
	{
		return $this->hasMany(BlogComment::class)->latest();
	}

	public function getAuthorDisplayNameAttribute(): string
	{
		return $this->getAttribute('author_name') ?: 'Admin';
	}
}
