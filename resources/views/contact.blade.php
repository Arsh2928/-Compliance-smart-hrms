@extends('layouts.landing')
@section('title', 'Contact Us')

@section('content')
<section class="landing-section">
    <div class="container landing-container">
        <div class="landing-section-head text-center">
            <div class="landing-badge" style="margin-left:auto;margin-right:auto;">
                <i class="bi bi-chat-dots"></i>
                <span>Contact</span>
            </div>
            <h1 class="landing-section-title" style="margin-top: 14px;">Talk to us</h1>
            <p class="landing-section-subtitle">Have a question or need help? Send a message.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="card landing-form-card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('contact.submit') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="How can we help you?" required></textarea>
                                </div>
                                <div class="col-12 d-flex gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-primary">Send Message</button>
                                    <a href="{{ route('home') }}" class="btn btn-secondary">Back to Home</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
