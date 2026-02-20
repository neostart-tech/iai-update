<?php

namespace App\Models;

use App\Models\Scopes\activeScope;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static self create(array $attributes)
 */
#[ScopedBy([activeScope::class])]
class Role extends Model
{
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	public $timestamps = false;

	protected $guarded = false;

	public function permissions(): BelongsToMany
	{
		return $this->belongsToMany(Permission::class)->using(PermissionRole::class);
	}
}
