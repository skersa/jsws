{{-- Sipariş sayfası değerlendirme formu. Param: $seller (User), $existing (SellerReview|null) --}}
@php $rvVal = $existing->rating ?? 5; @endphp
<div class="ord-box" data-testid="order-review-box" id="orderReviewBox">
    <div class="ord-box-title"><i class="bi bi-star-fill" style="color:#f59e0b"></i> Satıcıyı Değerlendir</div>
    <p class="pf-text-muted-sm" style="margin-bottom:12px">
        {{ $existing ? 'Değerlendirmeni güncelleyebilirsin.' : 'Ürünü teslim aldın! '.$seller->name.' için deneyimini puanla.' }}
    </p>

    @if(session('status'))<div class="alert alert-success" style="border-radius:10px">{{ session('status') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" style="border-radius:10px">{{ session('error') }}</div>@endif

    <form method="POST" action="{{ route('reviews.store', $seller->username) }}" class="rv-order-form">
        @csrf
        <div class="rv-order-stars" id="rvOrderStars" style="display:flex;gap:6px;font-size:26px;color:#f59e0b;margin-bottom:12px;cursor:pointer">
            @for($i = 1; $i <= 5; $i++)
                <i class="bi {{ $rvVal >= $i ? 'bi-star-fill' : 'bi-star' }}" data-val="{{ $i }}" data-testid="order-review-star-{{ $i }}"></i>
            @endfor
        </div>
        <input type="hidden" name="rating" id="rvOrderRating" value="{{ $rvVal }}">
        <div class="ord-field">
            <textarea name="comment" rows="3" placeholder="Satıcı, ürün ve kargo hakkında görüşün..." data-testid="order-review-comment">{{ $existing->comment ?? '' }}</textarea>
        </div>
        <button type="submit" class="btn-admin-pri" style="width:100%" data-testid="order-review-submit">
            <i class="bi bi-send me-1"></i> {{ $existing ? 'Değerlendirmeyi Güncelle' : 'Değerlendir' }}
        </button>
    </form>
</div>

<script src="{{ asset('assets/js/custom/review-form.js') }}"></script>
