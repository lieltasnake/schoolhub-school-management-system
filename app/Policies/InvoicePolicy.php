<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewFinance();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->canViewFinance()
            && $user->canAccessBranch($invoice->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageFinance()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($invoice->branch_id);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($invoice->branch_id);
    }
}
