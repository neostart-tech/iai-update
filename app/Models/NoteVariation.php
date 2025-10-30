<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo};

class NoteVariation extends Model
{
	use HasFactory;

	protected $fillable = [
		'note_id',
		'from',
		'to',
		'motif',
		'user_id',
		'annee_scolaire_id',
	];

	protected $guarded = [];

	public function note(): BelongsTo
	{
		return $this->belongsTo(Note::class);
	}
}
