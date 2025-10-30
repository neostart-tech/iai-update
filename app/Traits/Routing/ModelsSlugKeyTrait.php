<?php

namespace App\Traits\Routing;

trait ModelsSlugKeyTrait
{
	private bool $allWaysUpdate = false;

	public function getRouteKeyName(): string
	{
		return 'slug';
	}

	public function getSlugBaseKeyName(): string
	{
		return "nom";
	}

	public function hasSlugBaseKeyProvider(): bool
	{
		return true;
	}

	public function hasComplexSlug(): bool
	{
		return false;
	}

	public function allWaysUpdate(): bool
	{
		return $this->allWaysUpdate;
	}

	public function setAllWaysUpdate(bool $state = false): void
	{
		$this->allWaysUpdate = $state;
	}
}
