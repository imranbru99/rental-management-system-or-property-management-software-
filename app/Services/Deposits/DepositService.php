<?php

namespace App\Services\Deposits;

use App\Enums\DepositStatus;
use App\Models\DepositDeduction;
use App\Models\SecurityDeposit;
use InvalidArgumentException;

class DepositService
{
    public function deduct(SecurityDeposit $deposit, int $amount, string $reason): DepositDeduction
    {
        $this->assertHeld($deposit, $amount);

        $deduction = $deposit->deductions()->create([
            'amount' => $amount,
            'reason' => $reason,
        ]);

        $deposit->held_amount -= $amount;
        $deposit->status = $deposit->held_amount === 0
            ? DepositStatus::Forfeited
            : DepositStatus::PartialRefund;
        $deposit->save();

        return $deduction;
    }

    public function refund(SecurityDeposit $deposit, int $amount): SecurityDeposit
    {
        $this->assertHeld($deposit, $amount);

        $deposit->held_amount -= $amount;
        $deposit->status = $deposit->held_amount === 0
            ? DepositStatus::Refunded
            : DepositStatus::PartialRefund;

        if ($deposit->held_amount === 0) {
            $deposit->refunded_at = now();
        }

        $deposit->save();

        return $deposit->refresh();
    }

    protected function assertHeld(SecurityDeposit $deposit, int $amount): void
    {
        if ($amount < 1 || $amount > $deposit->held_amount) {
            throw new InvalidArgumentException('Amount must be between 1 and the held balance.');
        }
    }
}
