<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index() {
        $month = \App\Models\PerformanceRecord::max('month') ?? now()->format('Y-m');
        
        $allRecords = \App\Models\PerformanceRecord::with('employee.user')
            ->where('month', $month)
            ->get();

        $topRankers = $allRecords->sortByDesc(function($r) {
            return $r->final_score ?? $r->live_score ?? 0;
        })->take(3)->values();

        return view('welcome', compact('topRankers'));
    }

    public function leaderboard() {
        $month = \App\Models\PerformanceRecord::max('month') ?? now()->format('Y-m');
        
        $allRecords = \App\Models\PerformanceRecord::with('employee.user', 'employee.department')
            ->where('month', $month)
            ->get()
            ->sortByDesc(function($r) {
                return $r->final_score ?? $r->live_score ?? 0;
            })->values();

        $total = $allRecords->count();
        foreach ($allRecords as $index => $r) {
            $r->dynamic_rank = $index + 1;
            $r->dynamic_percentile = $total > 0 ? max(1, round((1 - ($index / $total)) * 100)) : 0;
            $r->calculated_score = round((float)($r->final_score ?? $r->live_score ?? 0), 1);
        }

        return view('public_leaderboard', compact('allRecords', 'month'));
    }

    public function about() {
        return view('about');
    }

    public function features() {
        return view('features');
    }

    public function contact() {
        return view('contact');
    }

    public function submitContact(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string'
        ]);

        $admins = \App\Models\User::where('role', 'admin')->get();
        if ($admins->isEmpty()) {
            return back()->with('error', 'Message could not be sent because no admin exists.');
        }

        $existingUser = \App\Models\User::where('email', $request->email)->first();

        foreach ($admins as $admin) {
            \App\Models\Message::create([
                'sender_id'   => $existingUser ? $existingUser->id : null,
                'receiver_id' => $admin->id,
                'subject'     => 'New Contact Form Submission',
                'body'        => $request->message,
                'guest_name'  => $existingUser ? null : $request->name,
                'guest_email' => $existingUser ? null : $request->email,
                'is_read'     => false,
            ]);
        }

        return back()->with('success', 'Your message has been sent successfully!');
    }
}
