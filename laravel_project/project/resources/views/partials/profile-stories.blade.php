@php
    \App\Models\Story::pruneExpired();

    $pfStories = $user->stories()->where('expires_at', '>', now())->orderBy('id')->get();
    $pfIsOwner = auth()->id() === $user->id;
@endphp

@if($pfStories->isNotEmpty())
<div class="pf-stories" data-testid="profile-stories">
    <div class="story-strip">

        @php $pfIds = $pfStories->pluck('id')->values(); @endphp
        <div class="story-item" data-testid="profile-story-user-{{ $user->id }}"
             data-story-uid="{{ $user->id }}"
             data-story-ids='@json($pfIds)'
             data-ring-unseen="{{ story_ring_style($pfStories->count()) }}"
             data-ring-seen="{{ story_ring_style($pfStories->count(), true) }}"
             onclick='openStoryViewer({{ $user->id }})'>
            <div class="story-ring" style="{{ story_ring_style($pfStories->count()) }}">
                <img src="{{ $user->profile_img }}" alt="{{ $user->name }}">
            </div>
            <span class="story-name">Hikayeler</span>
        </div>

    </div>
</div>

@include('partials.story-viewer')
@php
    $pfStoryPayload = [
        'name'    => $user->name,
        'avatar'  => $user->profile_img,
        'isOwner' => (bool) $pfIsOwner,
        'items'   => $pfStories->map(fn($st) => [
            'id'      => $st->id,
            'type'    => $st->media_type,
            'url'     => $st->url(),
            'caption' => $st->caption,
        ])->values(),
    ];
@endphp
<div class="story-source"
     data-user-id="{{ $user->id }}"
     data-story-payload='{{ json_encode($pfStoryPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_APOS|JSON_HEX_QUOT) }}'></div>
@endif
