@extends('layouts.app')
@section('title', 'Mesajlar')

@section('content')
<div class="msg-page py-4">
    <div class="msg-layout au-card">

        {{-- Konuşma listesi --}}
        <aside class="msg-list {{ $active ? 'has-active' : '' }}">
            <div class="msg-list-head">
                <i class="bi bi-chat-dots"></i> Mesajlar
            </div>
            <div class="msg-list-scroll">
                @forelse($conversations as $c)
                    @php
                        $peer = $c->other($user);
                        $unread = $c->unreadCountFor($user);
                        $isActive = $active && $active->id === $c->id;
                    @endphp
                    <a href="{{ route('messages.show', $c) }}"
                       class="msg-conv {{ $isActive ? 'active' : '' }}"
                       data-testid="conversation-item-{{ $c->id }}">
                        <img class="msg-avatar" src="{{ $peer?->profile_img }}" alt="{{ $peer?->name }}">
                        <div class="msg-conv-info">
                            <div class="msg-conv-name">{{ $peer?->name ?? 'Kullanıcı' }}</div>
                            <div class="msg-conv-last">{{ \Illuminate\Support\Str::limit($c->lastMessage?->body ?? 'Sohbeti başlat', 34) }}</div>
                        </div>
                        @if($unread > 0)
                            <span class="msg-unread">{{ $unread }}</span>
                        @endif
                    </a>
                @empty
                    <div class="msg-empty-list">
                        <i class="bi bi-inbox"></i>
                        <p>Henüz mesajın yok.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- Aktif sohbet --}}
        <section class="msg-chat {{ $active ? '' : 'is-empty' }}">
            @if($active)
                @php $peer = $active->other($user); @endphp
                <div class="msg-chat-head">
                    <a href="{{ route('messages.index') }}" class="msg-back" data-testid="messages-back"><i class="bi bi-arrow-left"></i></a>
                    <img class="msg-avatar" src="{{ $peer?->profile_img }}" alt="{{ $peer?->name }}">
                    <div>
                        <a href="{{ route('profile.public', $peer->username) }}" class="msg-chat-name">{{ $peer?->name }}</a>
                        <div class="msg-chat-sub">{{ '@' . $peer?->username }}</div>
                    </div>
                </div>

                <div class="msg-thread" id="msg-thread" data-conversation="{{ $active->id }}"
                     data-poll="{{ route('messages.poll', $active) }}">
                    @foreach($messages as $m)
                        <div class="msg-bubble {{ $m->sender_id === $user->id ? 'mine' : 'theirs' }}" data-mid="{{ $m->id }}">
                            <div class="msg-bubble-body">{{ $m->body }}</div>
                            <div class="msg-bubble-time">{{ $m->created_at->format('H:i') }}</div>
                        </div>
                    @endforeach
                </div>

                <form class="msg-compose" id="msg-form"
                      action="{{ route('messages.store', $active) }}" method="POST"
                      data-testid="message-form">
                    @csrf
                    <input type="text" name="body" id="msg-input" autocomplete="off"
                           placeholder="Bir mesaj yaz..." maxlength="2000"
                           data-testid="message-input" required>
                    <button type="submit" class="msg-send" data-testid="message-send">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            @else
                <div class="msg-placeholder">
                    <div class="msg-placeholder-icon"><i class="bi bi-chat-square-text"></i></div>
                    <div class="msg-placeholder-title">Bir sohbet seç</div>
                    <div class="msg-placeholder-sub">Soldan bir konuşma seçerek mesajlaşmaya başla.</div>
                </div>
            @endif
        </section>

    </div>
</div>

@if($active)
<script src="{{ asset('assets/js/custom/messages-index.js') }}"></script>
@endif
@endsection
