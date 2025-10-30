<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static self create(array $attributes)
 */
class Permission extends Model
{
	public $timestamps = false;

	protected $guarded = false;

	public function permissions(): BelongsToMany
	{
		return $this->belongsToMany(Role::class)->using(PermissionRole::class);
	}
}
