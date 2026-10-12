<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['role', 'branch'])->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), ['active', 'inactive'], true)) {
            $query->where('status', $request->input('status'));
        }

        $user = $request->user();
        $canFilterByBranch = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->integer('role_id'));
        }

        if ($canFilterByBranch && $request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        } elseif (! $canFilterByBranch) {
            $query->where('branch_id', $user->branch_id);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(),
            'branches' => $canFilterByBranch ? Branch::query()->orderBy('name')->get() : collect(),
            'canFilterByBranch' => $canFilterByBranch,
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => Role::orderBy('name')->get(),
            'branches' => $this->availableBranches(),
        ]);
    }

    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();
        $role = Role::findOrFail($validated['role_id']);

        if (! auth()->user()->canAssignRole($role->slug)) {
            abort(403, 'You are not allowed to assign that role.');
        }

        if (! empty($validated['branch_id']) && ! auth()->user()->canAccessBranch((int) $validated['branch_id'])) {
            abort(403, 'This branch is not assigned to your account.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'status' => $validated['status'],
            'locale' => 'en',
        ]);

        return redirect()->route('users.show', $user)->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'branches' => $this->availableBranches(),
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validated();
        $role = Role::findOrFail($validated['role_id']);

        if (! auth()->user()->canAssignRole($role->slug)) {
            abort(403, 'You are not allowed to assign that role.');
        }

        if (! empty($validated['branch_id']) && ! auth()->user()->canAccessBranch((int) $validated['branch_id'])) {
            abort(403, 'This branch is not assigned to your account.');
        }

        if ($user->hasRole('super-administrator') && ! auth()->user()->hasRole('super-administrator')) {
            abort(403, 'Super administrators can only be changed by a Super Administrator.');
        }

        DB::transaction(function () use ($validated, $role, $user): void {
            $activeSuperAdministrators = User::query()
                ->where('status', 'active')
                ->whereHas('role', fn ($query) => $query->where('slug', 'super-administrator'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['users.id']);
            $target = User::query()->with('role')->whereKey($user->id)->lockForUpdate()->firstOrFail();

            $wouldRemoveActiveSuperAdministrator = $target->status === 'active'
                && $target->hasRole('super-administrator')
                && ($role->slug !== 'super-administrator' || $validated['status'] !== 'active');

            if ($wouldRemoveActiveSuperAdministrator && $activeSuperAdministrators->count() <= 1) {
                throw ValidationException::withMessages([
                    'role_id' => 'At least one active Super Administrator must remain.',
                    'status' => 'The last active Super Administrator cannot be deactivated.',
                ]);
            }

            $target->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $role->id,
                'branch_id' => $validated['branch_id'] ?? null,
                'status' => $validated['status'],
            ]);

            if (! empty($validated['password'])) {
                $target->password = Hash::make($validated['password']);
            }

            $target->save();
        });

        return redirect()->route('users.show', $user)->with('success', 'User updated successfully.');
    }

    protected function availableBranches()
    {
        $currentUser = auth()->user();

        if ($currentUser->hasRole('super-administrator')) {
            return Branch::orderBy('name')->get();
        }

        if ($currentUser->hasRole('school-administrator')) {
            return Branch::orderBy('name')->get();
        }

        return Branch::whereKey($currentUser->branch_id)->get();
    }
}
