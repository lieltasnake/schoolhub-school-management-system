<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $user = $request->user();
        $query = Payment::query()->with(['invoice.student', 'invoice.branch', 'recordedBy']);

        $selectedBranchId = $user->branch_id;
        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->whereHas('invoice', fn ($q) => $q->where('branch_id', $user->branch_id));
        } elseif ($request->filled('branch_id')) {
            $selectedBranchId = $request->integer('branch_id');
            $query->whereHas('invoice', fn ($q) => $q->where('branch_id', $selectedBranchId));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method')->toString());
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

        return view('payments.index', [
            'payments' => $query->orderByDesc('id')->paginate(10)->withQueryString(),
            'branches' => $branches,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Payment::class);

        $user = $request->user();
        $selectedInvoice = null;

        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::query()
                ->with(['student', 'branch', 'academicYear', 'term'])
                ->whereKey($request->integer('invoice_id'))
                ->firstOrFail();

            if (! $user->canAccessBranch($selectedInvoice->branch_id)) {
                abort(403);
            }
        }

        $branches = $this->availableBranches(
            $user->branch_id,
            $user->hasAnyRole(['super-administrator', 'school-administrator']),
        );

        $branchId = $selectedInvoice ? $selectedInvoice->branch_id : ($user->branch_id ?: $branches->first()?->id);

        $unpaidInvoices = Invoice::query()
            ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->with(['student', 'academicYear'])
            ->orderByDesc('id')
            ->get();

        return view('payments.create', [
            'selectedInvoice' => $selectedInvoice,
            'unpaidInvoices' => $unpaidInvoices,
            'branches' => $branches,
        ]);
    }

    public function store(PaymentRequest $request, PaymentService $paymentService): RedirectResponse
    {
        $payment = $paymentService->recordPayment($request->validated(), $request->user());

        return redirect()->route('payments.show', $payment)
            ->with('success', "Payment {$payment->receipt_number} recorded successfully.");
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load([
            'invoice.student.guardians',
            'invoice.branch',
            'invoice.academicYear',
            'invoice.term',
            'invoice.items.feeCategory',
            'recordedBy',
        ]);

        return view('payments.show', compact('payment'));
    }

    public function reverse(Request $request, Payment $payment, PaymentService $paymentService): RedirectResponse
    {
        $this->authorize('reverse', $payment);

        $reason = $request->string('reason')->toString() ?: 'Administrative reversal';
        $paymentService->reversePayment($payment, $reason);

        return redirect()->route('payments.show', $payment)
            ->with('success', "Payment receipt {$payment->receipt_number} has been reversed.");
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
