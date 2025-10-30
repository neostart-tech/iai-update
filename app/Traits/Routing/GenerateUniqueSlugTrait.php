<?php

namespace App\Traits\Routing;

use Illuminate\Support\Str;

trait GenerateUniqueSlugTrait
{
	public static function bootGenerateUniqueSlugTrait(): void
	{
		$callback = function ($model) {
			if (!$model->hasSlugBaseKeyProvider()) {
				$model->slug = uniqid();
			} else {
				$slug = $model->generateUniqueSlug(Str::slug($model->{$model->getSlugBaseKeyName()}));
				if ($model->hasComplexSlug()) {
					$slug = uniqid($slug . '-');
				}
				$model->slug = $slug;
			}
		};

		static::saving(function ($model) use ($callback) {
			if ($model->allWaysUpdate()) {
				static::updating($callback);
			}
			static::creating($callback);
		});
	}

	public function generateUniqueSlug(string $slug): string
	{
		// Check if the slug already has a number at the end
		$originalSlug = $slug;
		$slugNumber = null;

		if (preg_match('/-(\d+)$/', $slug, $matches)) {
			$slugNumber = $matches[1];
			$slug = Str::replaceLast("-$slugNumber", '', $slug);
		}

		// Check if the modified slug already exists in the table
		$existingSlugs = $this->getExistingSlugs($slug, $this->getTable());

		if (!in_array($slug, $existingSlugs)) {
			// Slug is unique, no need to append numbers
			return $slug . ($slugNumber ? "-$slugNumber" : '');
		}

		// Increment the number until a unique slug is found
		$i = $slugNumber ? ((int)$slugNumber + 1) : 1;
		$uniqueSlugFound = false;

		while (!$uniqueSlugFound) {
			$newSlug = $slug . '-' . $i;

			if (!in_array($newSlug, $existingSlugs)) {
				// Unique slug found
				return $newSlug;
			}

			$i++;
		}

		// Fallback: return the original slug with a random number appended
		return $originalSlug . '-' . mt_rand(1000, 9999);
	}

	private function getExistingSlugs(string $slug, string $table): array
	{
		return $this->where('slug', 'LIKE', $slug . '%')
			->where('id', '!=', $this->id ?? null) // Exclude the current model's ID
			->pluck('slug')
			->toArray();
	}
}
