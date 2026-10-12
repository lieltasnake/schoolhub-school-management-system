<?php

namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Branch::class);

        $query = Branch::query();
        $user = $request->user();

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            if ($user->branch_id === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereKey($user->branch_id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $branches = $query->orderBy('name')->paginate(10)->appends($request->query());

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $this->authorize('create', Branch::class);

        return view('branches.create');
    }

    public function store(BranchRequest $request)
    {
        $this->authorize('create', Branch::class);

        $branch = Branch::create($request->validated());

        return redirect()->route('branches.show', $branch)->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $this->authorize('view', $branch);

        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        $this->authorize('update', $branch);

        return view('branches.edit', compact('branch'));
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $this->authorize('update', $branch);

        $branch->update($request->validated());

        return redirect()->route('branches.show', $branch)->with('success', 'Branch updated successfully.');
    }
}
