<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCirculation;
use App\Models\LibraryMember;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookCirculationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BookCirculation::class);

        $user = $request->user();
        $query = BookCirculation::query()->with([
            'book.library.branch',
            'libraryMember.student',
            'libraryMember.user',
            'issuedBy',
        ]);

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->whereHas('book.library', fn (Builder $b) => $b->where('branch_id', $user->branch_id));
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $b) use ($search) {
                $b->whereHas('book', fn (Builder $q) => $q->where('title', 'like', "%{$search}%")->orWhere('isbn', 'like', "%{$search}%"))
                    ->orWhereHas('libraryMember', fn (Builder $q) => $q->search($search));
            });
        }

        return view('library.circulations.index', [
            'circulations' => $query->orderByDesc('issue_date')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', BookCirculation::class);

        $user = $request->user();
        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        return view('library.circulations.form', [
            'books' => Book::query()
                ->where('status', 'available')
                ->where('available_copies', '>', 0)
                ->when($branchId, fn (Builder $b) => $b->whereHas('library', fn (Builder $l) => $l->where('branch_id', $branchId)))
                ->orderBy('title')->get(),
            'members' => LibraryMember::query()
                ->where('status', 'active')
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with(['student', 'user'])
                ->get()
                ->filter(fn (LibraryMember $m) => $m->canBorrowMore()),
            'defaultIssueDate' => now()->toDateString(),
            'defaultDueDate' => now()->addDays(14)->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BookCirculation::class);

        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'library_member_id' => ['required', 'integer', 'exists:library_members,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $book = Book::findOrFail($validated['book_id']);
        if ($book->available_copies <= 0 || $book->status !== 'available') {
            throw ValidationException::withMessages([
                'book_id' => 'This book is not currently available for lending.',
            ]);
        }

        $member = LibraryMember::findOrFail($validated['library_member_id']);
        if (! $member->canBorrowMore()) {
            throw ValidationException::withMessages([
                'library_member_id' => "This member has reached the maximum borrowing limit of {$member->max_borrow_limit} books.",
            ]);
        }

        $circulation = BookCirculation::create([
            'book_id' => $book->id,
            'library_member_id' => $member->id,
            'issued_by' => $request->user()->id,
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => 'issued',
            'fine_amount' => 0,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $book->decrement('available_copies');

        return redirect()->route('circulations.index')->with('success', 'Book issued successfully.');
    }

    public function returnBook(Request $request, BookCirculation $circulation): RedirectResponse
    {
        $this->authorize('update', $circulation);

        if ($circulation->status === 'returned') {
            return back()->with('error', 'This book has already been marked as returned.');
        }

        $validated = $request->validate([
            'return_date' => ['required', 'date'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $circulation->update([
            'return_date' => $validated['return_date'],
            'fine_amount' => $validated['fine_amount'] ?? 0,
            'status' => 'returned',
            'remarks' => $validated['remarks'] ? ($circulation->remarks ? $circulation->remarks."\n".$validated['remarks'] : $validated['remarks']) : $circulation->remarks,
        ]);

        $circulation->book->increment('available_copies');

        return redirect()->route('circulations.index')->with('success', 'Book returned successfully.');
    }

    public function destroy(BookCirculation $circulation): RedirectResponse
    {
        $this->authorize('delete', $circulation);

        if ($circulation->status === 'issued') {
            $circulation->book->increment('available_copies');
        }

        $circulation->delete();

        return redirect()->route('circulations.index')->with('success', 'Circulation record deleted.');
    }
}
