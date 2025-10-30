<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\{Role};
use Illuminate\View\View;

class RoleController extends Controller
{
	public function index(): View
	{
		return view('admin.roles.index')->with([
			'roles' => Role::with(['permissions'])->get()
		]);
	}

	public function create(): View
	{
		return view('admin.roles.create')->with([
			'role' => new Role()
		]);
	}

	public function store(Request $request): RedirectResponse
	{
		return to_route('admin.roles.index');
	}

	public function show(Role $role): View
	{
		return view('admin.roles.show', compact('role'));
	}

	public function edit(Role $role): View
	{
		return view('admin.roles.edit', compact('role'));
	}


	public function update(Request $request, Role $role): RedirectResponse
	{
		return to_route('admin.roles.index');
	}


	public function destroy(Role $role): RedirectResponse
	{
		return to_route('admin.roles.index');
	}


}
