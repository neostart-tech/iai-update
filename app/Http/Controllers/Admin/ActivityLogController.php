<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
	public function index(Request $request)
	{
		$query = Activity::query()->with('causer')->latest('id');

		if ($request->filled('user_id')) {
			$query->where('causer_id', $request->input('user_id'));
		}

		if ($request->filled('log_name')) {
			$query->where('log_name', $request->input('log_name'));
		}

		if ($request->filled('method')) {
			$query->where('properties->method', $request->input('method'));
		}

		if ($request->filled('date_from')) {
			$query->whereDate('created_at', '>=', $request->input('date_from'));
		}

		if ($request->filled('date_to')) {
			$query->whereDate('created_at', '<=', $request->input('date_to'));
		}

		if ($request->filled('search')) {
			$search = $request->input('search');
			$query->where(function ($q) use ($search) {
				$q->where('description', 'like', "%{$search}%")
					->orWhere('properties->path', 'like', "%{$search}%");
			});
		}

		$logs = $query->paginate($request->integer('per_page', 25));

		return ActivityLogResource::collection($logs);
	}

	/**
	 * Modules distincts (log_name) déjà présents, pour peupler le filtre côté front
	 * sans coder en dur la liste (les noms de modèles suivent class_basename()).
	 */
	public function modules()
	{
		return response()->json([
			'data' => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name'),
		]);
	}
}
