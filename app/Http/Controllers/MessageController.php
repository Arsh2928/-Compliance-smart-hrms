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

    public function reply(Request $request, Message $message)
    {
        // Ensure the user is the receiver of the original message to reply
        if ($message->receiver_id !== auth()->id()) {
            return abort(403, 'Unauthorized to reply to this message.');
        }

        $request->validate([
            'reply_body' => 'required|string',
        ]);

        $replySubject = str_starts_with($message->subject, 'Re:') ? $message->subject : 'Re: ' . $message->subject;

        if ($message->sender_id) {
            // Internal User (Employee)
            $replyMessage = Message::create([
                'sender_id'   => auth()->id(),
                'receiver_id' => $message->sender_id,
                'subject'     => $replySubject,
                'body'        => $request->reply_body . "\n\n--- Original Message ---\n" . $message->body,
                'is_read'     => false,
            ]);

            try {
                $originalSender = User::find($message->sender_id);
                if ($originalSender && $originalSender->email) {
                    Mail::to($originalSender->email)->send(new NewMessageMail($replyMessage));
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send reply email: " . $e->getMessage());
            }

            return back()->with('success', 'Reply sent successfully!');
        } else {
            // External Guest
            try {
                if ($message->guest_email) {
                    $emailBody = $request->reply_body . "\n\n--- Original Message ---\n" . $message->body;
                    Mail::raw($emailBody, function ($mail) use ($message, $replySubject) {
                        $mail->to($message->guest_email)
                             ->subject($replySubject);
                    });
                }
                return back()->with('success', 'Reply sent to guest email successfully!');
            } catch (\Exception $e) {
                \Log::error("Failed to send guest reply email: " . $e->getMessage());
                return back()->with('error', 'Failed to send email to guest.');
            }
        }
    }
}
