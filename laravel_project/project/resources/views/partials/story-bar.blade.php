@php
    // Süresi dolan hikayeleri fırsat buldukça fiziksel olarak temizle (cron olmayan ortamlar için)
    \App\Models\Story::pruneExpired();

    $storyUsers = \App\Models\User::whereHas('stories', fn($q) => $q->where('expires_at', '>', now()))
        ->with(['stories' => fn($q) => $q->where('expires_at', '>', now())->orderBy('id')])
        ->take(25)->get();

    // Giriş yapan kullanıcının kendi hikayeleri en başta gösterilsin
    if (auth()->check()) {
        $me = $storyUsers->firstWhere('id', auth()->id());
        if ($me) {
            $storyUsers = $storyUsers->reject(fn($u) => $u->id === auth()->id())->prepend($me)->values();
        }
    }

    $isSeller = auth()->check() && auth()->user()->isSeller();
@endphp

@if($storyUsers->isNotEmpty() || $isSeller)
<div class="story-bar" data-testid="story-bar">
    <div class="story-strip">

        @if($isSeller)
        <div class="story-item story-add" data-testid="story-add" onclick="openStoryUpload()">
            <div class="story-ring story-ring-add">
                <div class="story-add-inner"><i class="bi bi-plus-lg"></i></div>
            </div>
            <span class="story-name">Hikaye Ekle</span>
        </div>
        @endif

        @foreach($storyUsers as $su)
            @php $stIds = $su->stories->pluck('id')->values(); @endphp
            <div class="story-item" data-testid="story-user-{{ $su->id }}"
                 data-story-uid="{{ $su->id }}"
                 data-story-ids='@json($stIds)'
                 data-ring-unseen="{{ story_ring_style($su->stories->count()) }}"
                 data-ring-seen="{{ story_ring_style($su->stories->count(), true) }}"
                 onclick='openStoryViewer({{ $su->id }})'>
                <div class="story-ring" style="{{ story_ring_style($su->stories->count()) }}">
                    <img src="{{ $su->profile_img }}" alt="{{ $su->name }}">
                </div>
                <span class="story-name">{{ $su->id === auth()->id() ? 'Hikayen' : \Illuminate\Support\Str::limit($su->name, 10) }}</span>
            </div>
        @endforeach

    </div>
</div>

@include('partials.story-viewer')

@if($isSeller)
    @include('partials.story-upload')
@endif

@foreach($storyUsers as $su)
@php
    $storyPayload = [
        'name'    => $su->id === auth()->id() ? 'Hikayen' : $su->name,
        'avatar'  => $su->profile_img,
        'isOwner' => auth()->id() === $su->id,
        'items'   => $su->stories->map(fn($st) => [
            'id'      => $st->id,
            'type'    => $st->media_type,
            'url'     => $st->url(),
            'caption' => $st->caption,
        ])->values(),
    ];
@endphp
<div class="story-source"
     data-user-id="{{ $su->id }}"
     data-story-payload='{{ json_encode($storyPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_APOS|JSON_HEX_QUOT) }}'></div>
@endforeach
@endif
