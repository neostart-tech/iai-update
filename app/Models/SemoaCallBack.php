<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $attribute)
 */
class SemoaCallBack extends Model
{
	protected $fillable = ['data'];

	protected $casts = [
		'data' => 'array'
	];
}
