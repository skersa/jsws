@extends('layouts.app')
@section('title', $type === 'followers' ? $user->name . ' — Takipçiler' : $user->name . ' — Takip Edilenler')
@section('content')
<div class="fl-root">
    <div class="fl-header">
        <a href="{{ route('profile.public', $user->username) }}" class="fl-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="fl-header-name">{{ $user->name }}</div>
            <div class="fl-header-sub">&#64;{{ $user->username }}</div>
        </div>
    </div>

    <div class="fl-tabs">
        <a href="{{ route('profile.followers', $user->username) }}"
           class="fl-tab {{ $type === 'followers' ? 'active' : '' }}">
            Takipçiler
            <span class="fl-tab-count">{{ $followerCount }}</span>
        </a>
        <a href="{{ route('profile.following', $user->username) }}"
           class="fl-tab {{ $type === 'following' ? 'active' : '' }}">
            Takip Edilenler
            <span class="fl-tab-count">{{ $followingCount }}</span>
        </a>
    </div>

    <div class="fl-list">
        @forelse($followers as $follow)
            @php
                $person = $type === 'followers' ? $follow->follower : $follow->following;
                $isSelf = auth()->id() === $person->id;
                $isFollowingPerson = auth()->check() && !$isSelf
                    ? auth()->user()->isFollowing($person->id)
                    : false;
            @endphp

            <div class="fl-item">
                <a href="{{ route('profile.public', $person->username) }}" class="fl-item-left">
                    <div class="fl-avatar">
                        <img src="{{ $person->profile_img ?? 'https://ui-avatars.com/api/?name='.urlencode($person->name).'&background=155eef&color=fff&size=128' }}"
                             alt="{{ $person->name }}">
                    </div>
                    <div class="fl-info">
                        <div class="fl-name">{{ $person->name }}</div>
                        <div class="fl-handle">&#64;{{ $person->username }}</div>
                        @if($person->bio)
                            <div class="fl-bio">{{ Str::limit($person->bio, 55) }}</div>
                        @endif
                    </div>
                </a>

                @auth
                    @if(!$isSelf)
                        <button
                            class="fl-follow-btn {{ $isFollowingPerson ? 'following' : '' }}"
                            data-url="{{ route('follow.toggle', $person) }}"
                        >
                            @if($isFollowingPerson)
                                <i class="bi bi-person-check-fill"></i>
                                <span>Takip Ediliyor</span>
                            @else
                                <i class="bi bi-person-plus"></i>
                                <span>Takip Et</span>
                            @endif
                        </button>
                    @endif
                @endauth
            </div>
        @empty
            <div class="fl-empty">
                <div class="fl-empty-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="fl-empty-title">
                    {{ $type === 'followers' ? 'Henüz takipçi yok' : 'Henüz kimse takip edilmiyor' }}
                </div>
                <div class="fl-empty-sub">
                    {{ $type === 'followers' ? 'Bu kullanıcıyı takip eden kimse yok.' : 'Bu kullanıcı henüz kimseyi takip etmiyor.' }}
                </div>
            </div>
        @endforelse
    </div>

    @if($followers->hasPages())
        <div class="fl-pagination">
            {{ $followers->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('assets/js/custom/profile-follow-list.js') }}"></script>
@endpush

@endsection
