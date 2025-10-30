<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AnnouncementEtudiant extends Pivot
{
	public $incrementing = true;

	protected $fillable = [
		'etudiant_id',
		'announcement_id',
		'applied'
	];

	public $timestamps = true;
}
