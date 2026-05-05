<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewMessageMail;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::with('sender')
            ->where('receiver_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('messages.index', compact('messages'));
    }

    public function sent()
    {
        $messages = Message::with('receiver')
            ->where('sender_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('messages.sent', compact('messages'));
    }

    public function create()
    {
        // Only allow messaging active/approved users
        $users = User::where('id', '!=', auth()->id())
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();
            
        return view('messages.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,_id',
            'subject'     => 'required|string|max:255',
            'body'        => 'required|string',
        ]);

        $message = Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'subject'     => $request->subject,
            'body'        => $request->body,
            'is_read'     => false,
        ]);

        // Trigger Email Notification
        try {
            $receiver = User::find($request->receiver_id);
            if ($receiver && $receiver->email) {
                Mail::to($receiver->email)->send(new NewMessageMail($message));
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send message email: " . $e->getMessage());
            // Fail silently so the UI still works
        }

        return redirect()->route('messages.index')
            ->with('success', 'Message sent successfully.');
    }

    public function show(Message $message)
    {
        // Ensure the user is either the sender or receiver
        if ($message->sender_id !== auth()->id() && $message->receiver_id !== auth()->id()) {
            return abort(403, 'Unauthorized access to this message.');
        }

        if ($message->receiver_id === auth()->id() && !$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return view('messages.show', compact('message'));
    }
}
