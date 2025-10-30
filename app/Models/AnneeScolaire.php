<?php

namespace App\Models;

use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Model;

class AnneeScolaire extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

	public $timestamps = false;

	protected $guarded = false;

	protected $casts = [
    'active' => 'boolean',
];

public function scopeActive($q) { return $q->where('active', true); }
    public static function courante() { return static::active()->first(); }


}
