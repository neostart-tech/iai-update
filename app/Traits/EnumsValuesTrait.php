<?php

namespace App\Traits;

trait EnumsValuesTrait
{
	public static function values(): array
	{
		$values = [];
		foreach (static::cases() as $case) {
			$values[] = $case->value;
		}
		return $values;
	}
}