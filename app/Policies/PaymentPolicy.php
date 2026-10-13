<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewFinance();
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->canViewFinance()
            && $user->canAccessBranch($payment->invoice->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        $hasPermission = $user->canManageFinance() || $user->hasPermission('manage_receipts');

        return $hasPermission
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function reverse(User $user, Payment $payment): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($payment->invoice->branch_id);
    }
}
