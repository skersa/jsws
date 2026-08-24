<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\General\TopUpRequest;
use App\Services\BalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BalanceController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        $transactions = $this->balanceService->history($user, perPage: 15);

        $data = collect($transactions->items())->map(fn ($tx) => [
            'id'               => $tx->id,
            'url'              => route('general.balance.show', $tx),
            'is_credit'        => $tx->isCredit(),
            'description'      => $tx->description,
            'date'            => $tx->created_at->format('d.m.Y H:i'),
            'status'           => $tx->status,
            'status_label'     => $tx->status_label,
            'formatted_amount' => $tx->formatted_amount,
        ])->values();

        return Inertia::render('General/Balance/Index', [
            'is_seller'       => $user->isSeller(),
            'formatted_balance' => $user->formatted_balance,
            'transactions' => [
                'data'      => $data,
                'links'     => $transactions->linkCollection()->toArray(),
                'has_pages' => $transactions->hasPages(),
                'total'     => $transactions->total(),
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        $presets = [50, 100, 250, 500, 1000];

        return Inertia::render('General/Balance/Create', [
            'presets' => $presets,
        ]);
    }

    public function store(TopUpRequest $request): RedirectResponse
    {
        abort_unless($this->canTopUp(), 403);

        $user = $request->user();
        $amount = (float) $request->validated('amount');
        $method = $request->validated('payment_method');

        try {
            // --- DEMO: ödeme sağlayıcı simülasyonu ---
            $reference = 'DEMO-'.strtoupper(substr(md5(uniqid()), 0, 10));

            $transaction = $this->balanceService->credit(
                user: $user,
                amount: $amount,
                paymentMethod: $method,
                description: $this->paymentMethodLabel($method).' ile Bakiye Yükleme',
                reference: $reference,
                meta: [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            );

            return redirect()
                ->route('general.balance.index')
                ->with('success', number_format($transaction->amount, 2, ',', '.').' ₺ bakiyenize başarıyla eklendi.');

        } catch (\Exception $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Ödeme işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    public function withdrawCreate(): InertiaResponse
    {
        abort_unless(auth()->user()->isSeller(), 403);

        $presets = [100, 250, 500, 1000];
        $user = auth()->user();

        return Inertia::render('General/Balance/Withdraw', [
            'presets'           => $presets,
            'formatted_balance' => $user->formatted_balance,
        ]);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isSeller(), 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:10', 'max:100000'],
            'iban'   => ['required', 'string', 'min:16', 'max:34'],
        ], [
            'amount.required' => 'Çekilecek tutar zorunludur.',
            'amount.min'      => 'Minimum çekim tutarı 10 ₺\'dir.',
            'amount.max'      => 'Tek seferde maksimum 100.000 ₺ çekilebilir.',
            'iban.required'   => 'IBAN zorunludur.',
            'iban.min'        => 'Geçerli bir IBAN giriniz.',
        ]);

        try {
            $tx = $this->balanceService->debit(
                user: $user,
                amount: (float) $data['amount'],
                description: 'Para Çekme Talebi',
                meta: [
                    'iban'   => preg_replace('/\s+/', '', $data['iban']),
                    'method' => 'withdraw',
                    'ip'     => $request->ip(),
                ],
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Çekim işlemi sırasında bir hata oluştu.');
        }

        return redirect()
            ->route('general.balance.index')
            ->with('success', number_format($tx->amount, 2, ',', '.').' ₺ çekim talebiniz alındı. Tutar bakiyenizden düşüldü, 1-3 iş günü içinde IBAN adresinize gönderilecek.');
    }

    private function canTopUp(): bool
    {
        return auth()->check() && auth()->user()->hasRole('buyer');
    }

    public function show(Request $request, int $id): InertiaResponse
    {
        $transaction = $request->user()
            ->balanceTransactions()
            ->findOrFail($id);

        return Inertia::render('General/Balance/Show', [
            'transaction' => [
                'formatted_amount' => $transaction->formatted_amount,
                'is_credit'        => $transaction->isCredit(),
                'status'           => $transaction->status,
                'status_label'     => $transaction->status_label,
                'type_label'       => $transaction->type_label,
                'description'      => $transaction->description,
                'reference'        => $transaction->reference,
                'payment_method'   => $transaction->payment_method ?? '—',
                'balance_before'   => number_format($transaction->balance_before, 2, ',', '.').' ₺',
                'balance_after'    => number_format($transaction->balance_after, 2, ',', '.').' ₺',
                'created_at'       => $transaction->created_at->format('d.m.Y H:i:s'),
            ],
        ]);
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'credit_card' => 'Kredi Kartı',
            'bank_transfer' => 'Banka Havalesi',
            'papara' => 'Papara',
            default => 'Ödeme',
        };
    }
}
