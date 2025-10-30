<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserUniteValeur extends Pivot
{
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
