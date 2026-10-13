<?php

namespace App\Http\Controllers;

use App\Models\BookType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BookType::class);

        $query = BookType::query()->withCount('books');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn ($b) => $b->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        return view('library.book-types.index', [
            'bookTypes' => $query->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', BookType::class);

        return view('library.book-types.form', [
            'bookType' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BookType::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:book_types,code'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        BookType::create($validated);

        return redirect()->route('book-types.index')->with('success', 'Book type created successfully.');
    }

    public function edit(BookType $bookType): View
    {
        $this->authorize('update', $bookType);

        return view('library.book-types.form', [
            'bookType' => $bookType,
        ]);
    }

    public function update(Request $request, BookType $bookType): RedirectResponse
    {
        $this->authorize('update', $bookType);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('book_types', 'code')->ignore($bookType->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $bookType->update($validated);

        return redirect()->route('book-types.index')->with('success', 'Book type updated successfully.');
    }

    public function destroy(BookType $bookType): RedirectResponse
    {
        $this->authorize('delete', $bookType);

        if ($bookType->books()->exists()) {
            return back()->with('error', 'Cannot delete book category because books are assigned to it.');
        }

        $bookType->delete();

        return redirect()->route('book-types.index')->with('success', 'Book type deleted successfully.');
    }
}
