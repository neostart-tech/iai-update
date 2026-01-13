<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtudiantNiveau extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public function niveau()
	{
		return $this->belongsTo(Niveau::class);
	}
	public function etudiant()
	{
		return $this->belongsTo(Etudiant::class);
	}

	
	
}
