<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Notifications\TicketRepliedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'lastMessage'])->withCount('messages');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('q')) {
            $query->where('subject', 'like', '%'.$request->q.'%');
        }

        $tickets = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        $counts = [
            'all' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
        ];

        return Inertia::render('Admin/Support/Index', [
            'counts'  => $counts,
            'filters' => [
                'q'        => $request->q,
                'status'   => $request->status,
                'priority' => $request->priority,
            ],
            'tickets' => [
                'data' => collect($tickets->items())->map(fn ($t) => [
                    'id'             => $t->id,
                    'user_name'      => $t->user?->name,
                    'user_email'     => $t->user?->email,
                    'subject'        => Str::limit($t->subject, 48),
                    'messages_count' => $t->messages_count,
                    'priority_badge' => $t->priorityBadge(),
                    'priority_label' => $t->priorityLabel(),
                    'status_badge'   => $t->statusBadge(),
                    'status_label'   => $t->statusLabel(),
                    'updated_human'  => $t->updated_at->diffForHumans(),
                    'awaiting'       => $t->last_reply_by === 'user',
                    'show_url'       => route('admin.support.show', $t),
                ])->values(),
                'links'     => $tickets->linkCollection()->toArray(),
                'has_pages' => $tickets->hasPages(),
                'total'     => $tickets->total(),
            ],
        ]);
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load('messages.user', 'user');

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return Inertia::render('Admin/Support/Show', [
            'ticket' => [
                'id'             => $ticket->id,
                'subject'        => $ticket->subject,
                'subject_short'  => Str::limit($ticket->subject, 50),
                'status'         => $ticket->status,
                'status_badge'   => $ticket->statusBadge(),
                'status_label'   => $ticket->statusLabel(),
                'priority_badge' => $ticket->priorityBadge(),
                'priority_label' => $ticket->priorityLabel(),
                'category'       => ucfirst((string) $ticket->category),
                'is_closed'      => $ticket->isClosed(),
                'created_at'     => $ticket->created_at->format('d.m.Y H:i'),
                'updated_human'  => $ticket->updated_at->diffForHumans(),
                'messages_count' => $ticket->messages->count(),
                'reply_url'      => route('admin.support.reply', $ticket),
                'status_url'     => route('admin.support.status', $ticket),
                'index_url'      => route('admin.support.index'),
                'user' => [
                    'name'       => $ticket->user?->name,
                    'email'      => $ticket->user?->email,
                    'created_at' => $ticket->user?->created_at?->format('d.m.Y'),
                ],
                'messages' => $ticket->messages->map(fn ($msg) => [
                    'id'       => $msg->id,
                    'body'     => $msg->body,
                    'is_admin' => (bool) $msg->is_admin,
                    'user'     => $msg->user?->name,
                    'time'     => $msg->created_at->format('d.m.Y H:i'),
                    'avatar'   => $msg->user?->avatar ? asset('storage/' . $msg->user->avatar) : null,
                ])->values(),
            ],
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate(['body' => 'required|max:3000']);

        $message = $ticket->messages()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
            'is_admin' => true,
        ]);

        $ticket->update([
            'status' => 'in_progress',
            'last_reply_by' => 'admin',
        ]);

        $ticket->user->notify(new TicketRepliedNotification($ticket));

        $message->load('user');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'is_admin' => true,
                'user' => $message->user->name,
                'time' => $message->created_at->format('d.m.Y H:i'),
                'avatar'   => $message->user->avatar
                        ? asset('storage/' . $message->user->avatar)
                        : null,
            ],
        ]);
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate(['status' => 'required|in:open,in_progress,closed']);
        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Durum güncellendi.');
    }
}
