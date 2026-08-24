<?php

namespace App\Services;

use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BalanceService
{
    /**
     *
     * @throws \Throwable
     */
    public function credit(
        User   $user,
        float  $amount,
        string $paymentMethod = 'credit_card',
        string $description   = 'Bakiye Yükleme',
        ?array $meta          = null,
        ?string $reference    = null,
    ): BalanceTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Yükleme tutarı 0\'dan büyük olmalıdır.');
        }

        return DB::transaction(function () use ($user, $amount, $paymentMethod, $description, $meta, $reference) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            $balanceBefore = (float) $user->balance;
            $balanceAfter  = $balanceBefore + $amount;

            $user->increment('balance', $amount);

            return BalanceTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'credit',
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'description'    => $description,
                'reference'      => $reference ?? 'BAL-' . strtoupper(Str::random(12)),
                'status'         => 'completed',
                'payment_method' => $paymentMethod,
                'meta'           => $meta,
            ]);
        });
    }

    /**
     * Kullanıcı bakiyesinden para düş.
     *
     * @throws \Throwable
     * @throws \RuntimeException  Yetersiz bakiye
     */
    public function debit(
        User   $user,
        float  $amount,
        string $description = 'Bakiye Kullanımı',
        ?array $meta        = null,
    ): BalanceTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Düşüm tutarı 0\'dan büyük olmalıdır.');
        }

        return DB::transaction(function () use ($user, $amount, $description, $meta) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            if ((float) $user->balance < $amount) {
                throw new \RuntimeException('Yetersiz bakiye.');
            }

            $balanceBefore = (float) $user->balance;
            $balanceAfter  = $balanceBefore - $amount;

            $user->decrement('balance', $amount);

            return BalanceTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'debit',
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'description'    => $description,
                'status'         => 'completed',
                'reference'      => 'DBT-' . strtoupper(Str::random(12)),
                'meta'           => $meta,
            ]);
        });
    }

    public function history(User $user, int $perPage = 15)
    {
        return BalanceTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }
}
