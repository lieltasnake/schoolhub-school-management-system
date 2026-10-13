<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Classroom;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassroomController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Classroom::class);

        $user = $request->user();
        $query = Classroom::query()->with('branch');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $b) use ($search) {
                $b->where('name', 'like', "%{$search}%")
                    ->orWhere('room_number', 'like', "%{$search}%")
                    ->orWhere('building', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('classrooms.index', [
            'classrooms' => $query->orderBy('room_number')->paginate(15)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Classroom::class);

        $user = $request->user();

        return view('classrooms.form', [
            'classroom' => null,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Classroom::class);

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
            'room_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classrooms', 'room_number')->where('branch_id', $request->input('branch_id')),
            ],
            'building' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'maintenance', 'inactive'])],
        ]);

        Classroom::create($validated);

        return redirect()->route('classrooms.index')->with('success', 'Classroom created successfully.');
    }

    public function edit(Request $request, Classroom $classroom): View
    {
        $this->authorize('update', $classroom);

        $user = $request->user();

        return view('classrooms.form', [
            'classroom' => $classroom,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('update', $classroom);

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
            'room_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classrooms', 'room_number')
                    ->where('branch_id', $request->input('branch_id'))
                    ->ignore($classroom->id),
            ],
            'building' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'maintenance', 'inactive'])],
        ]);

        $classroom->update($validated);

        return redirect()->route('classrooms.index')->with('success', 'Classroom updated successfully.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        $this->authorize('delete', $classroom);

        if ($classroom->timetables()->exists()) {
            return back()->with('error', 'Cannot delete classroom because it has scheduled timetable slots.');
        }

        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Classroom deleted successfully.');
    }
}
