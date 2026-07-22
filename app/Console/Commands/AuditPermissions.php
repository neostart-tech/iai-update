<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class AuditPermissions extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'permissions:audit';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Compare les slugs 'can:xxx' réellement câblés sur les routes avec la table permissions, pour repérer un slug manquant (routes cassées pour tout le monde) ou une permission orpheline (jamais appliquée à aucune route).";

	public function handle(): int
	{
		$usedSlugs = [];

		foreach (Route::getRoutes() as $route) {
			foreach ($route->gatherMiddleware() as $middleware) {
				if (!is_string($middleware) || !str_starts_with($middleware, 'can:')) {
					continue;
				}

				$slug = explode(',', substr($middleware, 4))[0];
				$usedSlugs[$slug][] = $route->uri();
			}
		}

		$existingSlugs = Permission::whereNotNull('slug')->pluck('slug')->all();

		$missing = array_diff(array_keys($usedSlugs), $existingSlugs);
		$orphaned = array_diff($existingSlugs, array_keys($usedSlugs));

		// Un slug "manquant" n'est un vrai bug que s'il n'a pas non plus de
		// Gate::define de secours (ex: manage-gallery est résolu par un
		// Gate::define basé sur des ID de rôle, indépendamment de la table
		// permissions — fonctionnel, mais pas pilotable depuis l'UI Rôles).
		$brokenMissing = array_filter($missing, fn ($slug) => !Gate::has($slug));
		$fallbackMissing = array_diff($missing, $brokenMissing);

		$hasError = false;

		if (!empty($brokenMissing)) {
			$hasError = true;
			$this->error('Slugs utilisés par des routes, absents de la table permissions ET sans Gate::define de secours (routes cassées pour TOUS les rôles, y compris accès complet) :');
			foreach ($brokenMissing as $slug) {
				$this->line("  <fg=red>✗ {$slug}</> — utilisé par : " . implode(', ', $usedSlugs[$slug]));
			}
			$this->newLine();
		}

		if (!empty($fallbackMissing)) {
			$this->warn("Slugs absents de la table permissions mais couverts par un Gate::define en dur (fonctionnels, mais pas gérables depuis l'UI Rôles/Permissions) :");
			foreach ($fallbackMissing as $slug) {
				$this->line("  <fg=yellow>•</> {$slug} — utilisé par : " . implode(', ', $usedSlugs[$slug]));
			}
			$this->newLine();
		}

		if (!empty($orphaned)) {
			$this->warn('Permissions en base jamais câblées sur une route (à vérifier : oubli, ou gating prévu ailleurs/plus tard) :');
			foreach ($orphaned as $slug) {
				$this->line("  <fg=yellow>•</> {$slug}");
			}
			$this->newLine();
		}

		if (empty($missing) && empty($orphaned)) {
			$this->info('Tout est cohérent : chaque slug can:xxx utilisé sur une route existe en base, et chaque permission sluggée est câblée sur au moins une route.');
		}

		$this->newLine();
		$this->line(sprintf(
			'%d slug(s) distinct(s) utilisés sur les routes, %d permission(s) sluggée(s) en base.',
			count($usedSlugs),
			count($existingSlugs)
		));

		return $hasError ? self::FAILURE : self::SUCCESS;
	}
}
