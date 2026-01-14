<?php

namespace App\Policies;

use App\Models\GiftVoucher;
use App\Models\User;

class GiftVoucherPolicy
{
    public function view(User $user, GiftVoucher $voucher): bool
    {
        return (int) $voucher->user_id === (int) $user->id;
    }

    public function update(User $user, GiftVoucher $voucher): bool
    {
        return (int) $voucher->user_id === (int) $user->id;
    }
}
