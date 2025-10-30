<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EtudiantGroup extends Pivot
{
	protected $table = 'etudiant_group';

	public $incrementing = true;

	protected $fillable = [
		'etudiant_id',
		'group_id',
		'annee_scolaire_id',
	];

	public function group()
	{
		return $this->belongsTo(Group::class);
	}
	public function etudiant()
	{
		return $this->belongsTo(Etudiant::class);
	}

	
	
}
