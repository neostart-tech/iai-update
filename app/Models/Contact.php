<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	protected $fillable = [
		'nom',
		'tel',
		'email',
		'message',
		'status'
	];

	public function hasSlugBaseKeyProvider(): bool
	{
		return false;
	}
}
