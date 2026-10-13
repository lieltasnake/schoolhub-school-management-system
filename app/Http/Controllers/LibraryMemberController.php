<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LibraryMember;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryMemberController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LibraryMember::class);

        $user = $request->user();
        $query = LibraryMember::query()->with(['branch', 'student', 'user'])->withCount('activeCirculations');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('member_type')) {
            $query->where('member_type', $request->string('member_type')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        return view('library.members.index', [
            'members' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LibraryMember::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branchId = $canManageAll ? ($request->integer('branch_id') ?: $user->branch_id ?: Branch::first()?->id) : $user->branch_id;
        $branch = Branch::find($branchId);

        return view('library.members.form', [
            'member' => null,
            'branches' => Branch::query()
                ->when(! $canManageAll, fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'students' => Student::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->where('status', 'active')
                ->orderBy('first_name')->get(),
            'users' => User::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->where('status', 'active')
                ->orderBy('name')->get(),
            'suggestedMemberNumber' => LibraryMember::generateMemberNumber($branch),
            'selectedBranchId' => $branchId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LibraryMember::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'member_type' => ['required', Rule::in(['student', 'staff'])],
            'student_id' => ['nullable', 'required_if:member_type,student', 'integer', 'exists:students,id'],
            'user_id' => ['nullable', 'required_if:member_type,staff', 'integer', 'exists:users,id'],
            'member_number' => ['nullable', 'string', 'max:50', 'unique:library_members,member_number'],
            'max_borrow_limit' => ['required', 'integer', 'min:1', 'max:20'],
            'status' => ['required', Rule::in(['active', 'suspended', 'expired'])],
        ]);

        if (empty($validated['member_number'])) {
            $branch = Branch::find($validated['branch_id']);
            $validated['member_number'] = LibraryMember::generateMemberNumber($branch);
        }

        LibraryMember::create($validated);

        return redirect()->route('library-members.index')->with('success', 'Library member registered successfully.');
    }

    public function edit(Request $request, LibraryMember $libraryMember): View
    {
        $this->authorize('update', $libraryMember);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branchId = $libraryMember->branch_id;

        return view('library.members.form', [
            'member' => $libraryMember,
            'branches' => Branch::query()
                ->when(! $canManageAll, fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'students' => Student::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('first_name')->get(),
            'users' => User::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('name')->get(),
            'suggestedMemberNumber' => $libraryMember->member_number,
            'selectedBranchId' => $branchId,
        ]);
    }

    public function update(Request $request, LibraryMember $libraryMember): RedirectResponse
    {
        $this->authorize('update', $libraryMember);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'member_type' => ['required', Rule::in(['student', 'staff'])],
            'student_id' => ['nullable', 'required_if:member_type,student', 'integer', 'exists:students,id'],
            'user_id' => ['nullable', 'required_if:member_type,staff', 'integer', 'exists:users,id'],
            'member_number' => ['required', 'string', 'max:50', Rule::unique('library_members', 'member_number')->ignore($libraryMember->id)],
            'max_borrow_limit' => ['required', 'integer', 'min:1', 'max:20'],
            'status' => ['required', Rule::in(['active', 'suspended', 'expired'])],
        ]);

        $libraryMember->update($validated);

        return redirect()->route('library-members.index')->with('success', 'Library member updated successfully.');
    }

    public function destroy(LibraryMember $libraryMember): RedirectResponse
    {
        $this->authorize('delete', $libraryMember);

        if ($libraryMember->activeCirculations()->exists()) {
            return back()->with('error', 'Cannot delete member who has unreturned borrowed books.');
        }

        $libraryMember->delete();

        return redirect()->route('library-members.index')->with('success', 'Library member deleted successfully.');
    }
}
