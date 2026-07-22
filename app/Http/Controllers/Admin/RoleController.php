<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use App\Models\{Permission, Role};

class RoleController extends Controller
{
	public function index()
	{
		return RoleResource::collection(Role::with(['permissions'])->get());
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'nom' => ['required', 'string', 'max:255', 'unique:roles,nom'],
			'permissions' => ['array'],
			'permissions.*' => ['integer', 'exists:permissions,id'],
		]);

		$role = Role::create(['nom' => $data['nom'], 'active' => true]);
		$role->permissions()->sync($data['permissions'] ?? []);

		return new RoleResource($role->load('permissions'));
	}

	public function show(Role $role)
	{
		return new RoleResource($role->load('permissions'));
	}

	public function update(Request $request, Role $role)
	{
		$data = $request->validate([
			'nom' => ['required', 'string', 'max:255', 'unique:roles,nom,' . $role->id],
		]);

		$role->update(['nom' => $data['nom']]);

		return new RoleResource($role->load('permissions'));
	}

	public function destroy(Role $role)
	{
		$role->permissions()->detach();
		$role->delete();

		return response()->json(['message' => 'Rôle supprimé.']);
	}

	/**
	 * Assigne l'ensemble des permissions d'un rôle en une fois (remplace l'existant).
	 * C'est le cœur du système de permissions dynamique : plus besoin de coder en dur
	 * qui a accès à quoi, un administrateur choisit ici les permissions du rôle.
	 */
	public function syncPermissions(Request $request, Role $role)
	{
		$data = $request->validate([
			'permissions' => ['array'],
			'permissions.*' => ['integer', 'exists:permissions,id'],
		]);

		$role->permissions()->sync($data['permissions'] ?? []);

		return new RoleResource($role->load('permissions'));
	}

	public function availablePermissions()
	{
		return PermissionResource::collection(Permission::whereNotNull('slug')->orderBy('nom')->get());
	}
}
