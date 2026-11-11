@extends('website.layouts.app')

@php($seoContent = $page)

@section('content')
    <div class="page-shell">
        <div class="site-container">
            <div class="page-hero page-hero--center mb-12">
                <span class="site-pill">{{ $page?->hero_eyebrow ?: 'Start a conversation' }}</span>
                <h1 class="site-heading mt-6">{{ $page?->hero_heading ?: 'Let’s talk about your next initiative.' }}</h1>
                <p class="page-lead mt-5">{{ $page?->hero_description ?: 'Tell us what you are working toward and we will bring the right people into the conversation.' }}</p>
            </div>
            <div class="grid gap-6 lg:grid-cols-[.75fr_1.25fr]">
                <div class="content-card h-fit p-7 sm:p-9">
                    <h2 class="text-2xl font-bold text-[#172033]">Connect with Rexxsol</h2>
                    <div class="mt-7 space-y-5 text-sm text-[#64748b]">
                        @if (($settings['contact.business_email'] ?? null)) <p><span class="form-label">Email</span><a href="mailto:{{ $settings['contact.business_email'] }}" class="text-[#536be5]">{{ $settings['contact.business_email'] }}</a></p> @endif
                        @if (($settings['contact.phone'] ?? null)) <p><span class="form-label">Phone</span>{{ $settings['contact.phone'] }}</p> @endif
                        @if (($settings['contact.address'] ?? null)) <p><span class="form-label">Address</span>{{ $settings['contact.address'] }}</p> @endif
                    </div>
                </div>
                <div class="content-card p-7 sm:p-9">
                    @if (session('success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
                    @endif
                    <form method="POST" action="{{ route('website.contact.store') }}" class="mt-1 space-y-5">
                        @csrf
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div><label for="name" class="form-label">Name</label><input id="name" name="name" value="{{ old('name') }}" class="form-control" required>@error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                            <div><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" required>@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div><label for="phone" class="form-label">Phone</label><input id="phone" name="phone" value="{{ old('phone') }}" class="form-control"></div>
                            <div><label for="company" class="form-label">Company</label><input id="company" name="company" value="{{ old('company') }}" class="form-control"></div>
                        </div>
                        <div><label for="subject" class="form-label">Subject</label><input id="subject" name="subject" value="{{ old('subject') }}" class="form-control" required>@error('subject')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label for="message" class="form-label">Message</label><textarea id="message" name="message" rows="6" class="form-control" required>{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <button type="submit" class="site-button">Send message <span aria-hidden="true">&#8594;</span></button>
                    </form>
                </div>
            </div>
        </div>
@endsection
