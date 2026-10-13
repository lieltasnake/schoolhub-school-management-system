<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Library;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Library::class);

        $user = $request->user();
        $query = Library::query()->with('branch')->withCount('books');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn (Builder $b) => $b->where('name', 'like', "%{$search}%")->orWhere('location', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('library.libraries.index', [
            'libraries' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Library::class);

        $user = $request->user();

        return view('library.libraries.form', [
            'library' => null,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Library::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        Library::create($validated);

        return redirect()->route('libraries.index')->with('success', 'Library created successfully.');
    }

    public function edit(Request $request, Library $library): View
    {
        $this->authorize('update', $library);

        $user = $request->user();

        return view('library.libraries.form', [
            'library' => $library,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Library $library): RedirectResponse
    {
        $this->authorize('update', $library);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $library->update($validated);

        return redirect()->route('libraries.index')->with('success', 'Library updated successfully.');
    }

    public function destroy(Library $library): RedirectResponse
    {
        $this->authorize('delete', $library);

        if ($library->books()->exists()) {
            return back()->with('error', 'Cannot delete library because it contains books.');
        }

        $library->delete();

        return redirect()->route('libraries.index')->with('success', 'Library deleted successfully.');
    }
}
