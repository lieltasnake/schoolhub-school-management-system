<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookType;
use App\Models\Branch;
use App\Models\Library;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Book::class);

        $user = $request->user();
        $query = Book::query()->with(['library.branch', 'bookType']);

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->whereHas('library', fn (Builder $b) => $b->where('branch_id', $user->branch_id));
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('library', fn (Builder $b) => $b->where('branch_id', $request->integer('branch_id')));
        }

        if ($request->filled('library_id')) {
            $query->where('library_id', $request->integer('library_id'));
        }

        if ($request->filled('book_type_id')) {
            $query->where('book_type_id', $request->integer('book_type_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        return view('library.books.index', [
            'books' => $query->orderBy('title')->paginate(15)->withQueryString(),
            'libraries' => Library::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('name')->get(),
            'bookTypes' => BookType::query()->orderBy('name')->get(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Book::class);

        $user = $request->user();
        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        return view('library.books.form', [
            'book' => null,
            'libraries' => Library::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('name')->get(),
            'bookTypes' => BookType::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Book::class);

        $validated = $request->validate([
            'library_id' => ['required', 'integer', 'exists:libraries,id'],
            'book_type_id' => ['required', 'integer', 'exists:book_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:50'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'edition' => ['nullable', 'string', 'max:50'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'total_copies' => ['required', 'integer', 'min:1'],
            'available_copies' => ['required', 'integer', 'min:0', 'lte:total_copies'],
            'status' => ['required', Rule::in(['available', 'borrowed', 'reserved', 'lost', 'damaged'])],
        ]);

        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Book added to catalog successfully.');
    }

    public function show(Book $book): View
    {
        $this->authorize('view', $book);

        $book->load(['library.branch', 'bookType', 'circulations.libraryMember.student', 'circulations.libraryMember.user', 'circulations.issuedBy']);

        return view('library.books.show', [
            'book' => $book,
            'recentCirculations' => $book->circulations()->latest('issue_date')->take(10)->get(),
        ]);
    }

    public function edit(Request $request, Book $book): View
    {
        $this->authorize('update', $book);

        $user = $request->user();
        $branchId = $book->library->branch_id;

        return view('library.books.form', [
            'book' => $book,
            'libraries' => Library::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('name')->get(),
            'bookTypes' => BookType::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validate([
            'library_id' => ['required', 'integer', 'exists:libraries,id'],
            'book_type_id' => ['required', 'integer', 'exists:book_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:50'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'edition' => ['nullable', 'string', 'max:50'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'total_copies' => ['required', 'integer', 'min:1'],
            'available_copies' => ['required', 'integer', 'min:0', 'lte:total_copies'],
            'status' => ['required', Rule::in(['available', 'borrowed', 'reserved', 'lost', 'damaged'])],
        ]);

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        if ($book->activeCirculations()->exists()) {
            return back()->with('error', 'Cannot delete book with active circulations (borrowed copies).');
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }
}
