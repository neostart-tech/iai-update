<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Configuration commune du journal d'activité (spatie/laravel-activitylog) pour
 * les modèles sensibles : uniquement les attributs modifiés, pas d'entrée quand
 * rien n'a changé, mots de passe/tokens jamais journalisés.
 */
trait LogsActivityWithDefaults
{
	use LogsActivity;

	public function getActivitylogOptions(): LogOptions
	{
		// logAll() (pas logFillable()/logUnguarded()) : ces modèles utilisent
		// `$guarded = false` sans `$fillable` défini, donc les options par défaut
		// de spatie ne trouvaient aucun attribut à journaliser — logAll() (logOnly
		// avec un joker '*') fonctionne indépendamment de cette configuration.
		return LogOptions::defaults()
			->logAll()
			->logOnlyDirty()
			->dontSubmitEmptyLogs()
			->logExcept(['updated_at', 'remember_token', 'password'])
			->useLogName(class_basename($this));
	}
}
