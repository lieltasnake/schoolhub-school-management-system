<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\FeeDiscount;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\Section;
use App\Models\Student;
use App\Models\Term;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $user = $request->user();
        $query = Invoice::query()->with(['student', 'academicYear', 'term', 'branch']);

        $selectedBranchId = $user->branch_id;
        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $selectedBranchId = $request->integer('branch_id');
            $query->where('branch_id', $selectedBranchId);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->integer('term_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        $branches = $this->availableBranches(
            $user->branch_id,
            $user->hasAnyRole(['super-administrator', 'school-administrator']),
        );

        return view('invoices.index', [
            'invoices' => $query->orderByDesc('id')->paginate(10)->withQueryString(),
            'branches' => $branches,
            'academicYears' => AcademicYear::query()
                ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
                ->orderBy('name')
                ->get(),
            'terms' => Term::query()
                ->when($selectedBranchId, fn (Builder $q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('branch_id', $selectedBranchId)))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Invoice::class);

        $user = $request->user();
        $branches = $this->availableBranches(
            $user->branch_id,
            $user->hasAnyRole(['super-administrator', 'school-administrator']),
        );

        $branchId = $request->integer('branch_id') ?: ($user->branch_id ?: $branches->first()?->id);

        return view('invoices.create', [
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'students' => Student::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get(),
            'academicYears' => AcademicYear::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(),
            'grades' => Grade::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->orderBy('level')
                ->get(),
            'sections' => Section::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(),
            'terms' => Term::query()
                ->when($branchId, fn (Builder $q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('branch_id', $branchId)))
                ->orderBy('name')
                ->get(),
            'feeCategories' => FeeCategory::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'feeDiscounts' => FeeDiscount::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(InvoiceRequest $request, InvoiceService $invoiceService): RedirectResponse
    {
        $user = $request->user();

        if ($request->boolean('is_batch')) {
            $result = $invoiceService->batchGenerateInvoices(
                $request->integer('branch_id'),
                $request->integer('academic_year_id'),
                $request->integer('grade_id'),
                $request->filled('term_id') ? $request->integer('term_id') : null,
                $request->string('due_date')->toString(),
                $request->filled('section_id') ? $request->integer('section_id') : null,
                $user,
            );

            $msg = "Batch invoicing complete: {$result['generated']} invoice(s) created, {$result['skipped']} skipped (already invoiced).";

            return redirect()->route('invoices.index')->with('success', $msg);
        }

        $items = $request->input('items', []);
        $discounts = $request->input('discounts', []);

        $invoice = $invoiceService->createInvoice([
            'branch_id' => $request->integer('branch_id'),
            'student_id' => $request->integer('student_id'),
            'academic_year_id' => $request->integer('academic_year_id'),
            'term_id' => $request->filled('term_id') ? $request->integer('term_id') : null,
            'due_date' => $request->string('due_date')->toString(),
            'notes' => $request->input('notes'),
        ], $items, $discounts, $user);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'student.guardians',
            'branch',
            'academicYear',
            'term',
            'items.feeCategory',
            'discounts.feeDiscount',
            'payments.recordedBy',
            'creator',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    public function cancel(Invoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        if ((float) $invoice->paid_amount > 0) {
            return back()->with('error', 'Cannot cancel an invoice with recorded payments.');
        }

        $invoice->update(['status' => 'cancelled']);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} has been cancelled.");
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
