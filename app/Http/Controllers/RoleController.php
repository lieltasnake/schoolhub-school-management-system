<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query()->withCount('users')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($roles) use ($search): void {
                $roles->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return view('roles.index', [
            'roles' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.form', [
            'role' => null,
            'permissions' => Role::CUSTOM_PERMISSION_LABELS,
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        Role::create($request->validated());

        return redirect()->route('roles.index')->with('success', 'Custom role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('roles.form', [
            'role' => $role,
            'permissions' => Role::CUSTOM_PERMISSION_LABELS,
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);
        $role->update($request->validated());

        return redirect()->route('roles.index')->with('success', 'Custom role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Custom role deleted successfully.');
    }
}
