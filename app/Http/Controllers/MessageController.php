<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::where('receiver_id', Auth::id())
            ->where('receiver_deleted', false)
            ->with('sender:id,username')
            ->orderByDesc('created_at')
            ->paginate(20);
        return Inertia::render('Messages/Index', ['messages' => $messages]);
    }

    public function show(int $id)
    {
        $message = Message::where('id', $id)
            ->where(fn($q) => $q->where('receiver_id', Auth::id())->orWhere('sender_id', Auth::id()))
            ->with('sender:id,username', 'receiver:id,username')
            ->firstOrFail();
        if ($message->receiver_id === Auth::id() && !$message->read) {
            $message->update(['read' => true]);
        }
        return Inertia::render('Messages/Show', ['message' => $message]);
    }

    public function create(int $userId)
    {
        $receiver = User::findOrFail($userId);
        return Inertia::render('Messages/Create', ['receiver' => $receiver]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:5000'],
        ]);
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'subject' => $request->subject,
            'body' => $request->body,
        ]);
        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();
        return redirect()->route('messages.index')->with('success', 'Message sent.');
    }

    public function destroy(int $id)
    {
        $message = Message::findOrFail($id);
        if ($message->receiver_id === Auth::id()) $message->update(['receiver_deleted' => true]);
        elseif ($message->sender_id === Auth::id()) $message->update(['sender_deleted' => true]);
        if ($message->receiver_deleted && $message->sender_deleted) $message->delete();
        return back()->with('success', 'Message deleted.');
    }
}
