<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static self create(array $attributes)
 */
class PermissionRole extends Pivot
{
	protected $guarded = false;

	public $timestamps = false;
}
