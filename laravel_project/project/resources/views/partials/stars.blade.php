@php $r = round(($rating ?? 0) * 2) / 2; @endphp
<span class="stars">
    @for($i = 1; $i <= 5; $i++)
        @if($r >= $i)
            <i class="bi bi-star-fill"></i>
        @elseif($r >= $i - 0.5)
            <i class="bi bi-star-half"></i>
        @else
            <i class="bi bi-star"></i>
        @endif
    @endfor
</span>
