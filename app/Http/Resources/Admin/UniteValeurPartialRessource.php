<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UniteValeurPartialRessource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'nom' => $this->resource->nom,
			'slug' => $this->resource->slug,
			'id' => $this->resource->id,
		];
	}
}
