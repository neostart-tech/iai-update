<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Advertiser extends Model
{
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait, Notifiable; 

	public function getSlugBaseKeyName(): string
	{
		return 'nom';
	}

	public function hasComplexSlug(): bool
	{
		return true;
	}

	protected $fillable = [
		'nom',
		'email',
		'details',
		'site',
		'ville',
		'logo'
	];

	public $timestamps = false;

	public function announcements(): HasMany
	{
		return $this->hasMany(Announcement::class);
	}

	public function FileFulllPath(){
		return asset(Storage::url($this->logo));
	}

}
