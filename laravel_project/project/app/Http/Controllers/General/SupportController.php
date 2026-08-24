<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->with('lastMessage')
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->paginate(10);

        return Inertia::render('General/Support/Index', [
            'tickets' => [
                'data' => collect($tickets->items())->map(fn ($t) => [
                    'id'             => $t->id,
                    'subject'        => Str::limit($t->subject, 50),
                    'last_message'   => $t->lastMessage ? Str::limit($t->lastMessage->body, 60) : null,
                    'category'       => ucfirst($t->category),
                    'priority_label' => $t->priorityLabel(),
                    'priority_badge' => $t->priorityBadge(),
                    'status_label'   => $t->statusLabel(),
                    'status_badge'   => $t->statusBadge(),
                    'updated_at'     => $t->updated_at->diffForHumans(),
                    'show_url'       => route('support.show', $t),
                ])->values(),
                'links'     => $tickets->linkCollection()->toArray(),
                'has_pages' => $tickets->hasPages(),
                'total'     => $tickets->total(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('General/Support/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:150',
            'category' => 'required|in:general,billing,auction,technical,other',
            'priority' => 'required|in:low,medium,high',
            'body' => 'required|string|min:10|max:3000',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
            'last_reply_at' => now(),
            'last_reply_by' => 'user',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'body' => $data['body'],
            'is_admin' => false,
        ]);

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Destek talebiniz oluşturuldu.');
    }

    public function show(SupportTicket $ticket)
    {
        abort_if($ticket->user_id !== Auth::id(), 403);

        $ticket->load('messages.user');

        return Inertia::render('General/Support/Show', [
            'ticket' => [
                'id'             => $ticket->id,
                'subject'        => $ticket->subject,
                'priority_label' => $ticket->priorityLabel(),
                'priority_badge' => $ticket->priorityBadge(),
                'status_label'   => $ticket->statusLabel(),
                'status_badge'   => $ticket->statusBadge(),
                'is_open'        => $ticket->isOpen(),
                'reply_url'      => route('support.reply', $ticket),
                'close_url'      => route('support.close', $ticket),
                'messages'       => $ticket->messages->map(fn ($m) => [
                    'id'       => $m->id,
                    'body'     => $m->body,
                    'is_admin' => (bool) $m->is_admin,
                    'author'   => $m->is_admin ? 'Destek Ekibi' : $m->user->name,
                    'avatar'   => $m->user->avatar ? asset('storage/' . $m->user->avatar) : asset('assets/media/placeholder.svg'),
                    'time'     => $m->created_at->format('d.m.Y H:i'),
                ])->values(),
            ],
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless(auth()->id() === $ticket->user_id, 403);
        abort_if($ticket->isClosed(), 403);

        $request->validate(['body' => 'required|max:3000']);

        $message = $ticket->messages()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
            'is_admin' => false,
        ]);

        $ticket->update(['last_reply_by' => 'user']);

        $message->load('user');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'is_admin' => false,
                'user' => $message->user->name,
                'time' => $message->created_at->format('d.m.Y H:i'),
                'avatar'   => $message->user->avatar
                        ? asset('storage/' . $message->user->avatar)
                        : null,
            ],
        ]);
    }

    public function close(SupportTicket $ticket)
    {
        abort_if($ticket->user_id !== Auth::id(), 403);

        $ticket->update(['status' => 'closed']);

        return back()->with('success', 'Talep kapatıldı.');
    }
}
