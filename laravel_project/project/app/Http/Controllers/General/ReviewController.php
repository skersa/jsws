<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerReview;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, User $user)
    {
        $reviewer = auth()->user();

        if ($reviewer->id === $user->id) {
            return back()->with('error', 'Kendinizi puanlayamazsınız.');
        }

        if (! $user->hasRole('seller')) {
            return back()->with('error', 'Yalnızca satıcılar puanlanabilir.');
        }

        if (! $reviewer->hasCompletedOrderFrom($user->id)) {
            return back()->with('error', 'Yalnızca teslim aldığınız (tamamlanan) siparişler için değerlendirme yapabilirsiniz.');
        }

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Değerlendirmeyi en son tamamlanan siparişe bağla (referans için)
        $orderId = Order::where('buyer_id', $reviewer->id)
            ->where('seller_id', $user->id)
            ->where('status', 'completed')
            ->latest()
            ->value('id');

        SellerReview::updateOrCreate(
            ['seller_id' => $user->id, 'reviewer_id' => $reviewer->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null, 'order_id' => $orderId]
        );

        return back()->with('status', 'Değerlendirmeniz kaydedildi. Teşekkürler!');
    }
}
