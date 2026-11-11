<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>

<body class="rex-login-body">
    @php
        $authBgImages = array_values(array_filter(
            array_map(static fn ($path) => basename($path), glob(public_path('images/auth/cms-login-bg-*'))),
            static fn ($file) => is_string($file) && $file !== ''
        ));

        $authBgCount = count($authBgImages);
        $authBgIndex = session('rex_login_bg_index', 0);

        if ($authBgCount > 0) {
            $selectedAuthBg = $authBgImages[$authBgIndex % $authBgCount];
            session(['rex_login_bg_index' => ($authBgIndex + 1) % $authBgCount]);
        } else {
            $selectedAuthBg = 'rexxsol-login-office-bg.png';
        }
    @endphp

    <svg xmlns="http://www.w3.org/2000/svg"
         width="0"
         height="0"
         aria-hidden="true"
         focusable="false"
         style="position:absolute;overflow:hidden">
        <defs>
            <symbol id="rex-icon-mail" viewBox="0 0 24 24">
                <rect x="3" y="5" width="18" height="14" rx="2"/>
                <path d="m3 6 9 7 9-7"/>
            </symbol>

            <symbol id="rex-icon-login" viewBox="0 0 24 24">
                <path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/>
                <path d="M3 12h12m-4-4 4 4-4 4"/>
            </symbol>
        </defs>
    </svg>

    <div class="rex-login-page">
        <aside
            class="rex-login-visual"
            aria-label="About Rexxsol CMS"
            style="background-image: url({{ asset('images/auth/' . $selectedAuthBg) }})"
        >
            <div class="rex-login-visual-inner">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Rexxsol"
                    class="rex-login-logo"
                    decoding="async"
                >

                <div class="rex-login-copy">
                    <div class="rex-login-eyebrow">
                        <span class="rex-login-eyebrow-line" aria-hidden="true"></span>
                        <span>REXXSOL CMS</span>
                    </div>

                    <h1 class="rex-login-heading">
                        Manage Your Website<br>
                        with Confidence
                    </h1>

                    <p class="rex-login-description">
                        Everything you need to manage your
                        website from one secure workspace.
                    </p>
                </div>
            </div>
        </aside>

        <main class="rex-login-form-panel">
            <section class="rex-login-card rex-forgot-password-card" aria-labelledby="rex-forgot-password-title">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Rexxsol"
                    class="rex-login-card-logo"
                    decoding="async"
                >

                <h2 id="rex-forgot-password-title" class="rex-login-card-title">
                    Forgot your password?
                </h2>

                <p class="rex-login-card-subtitle">
                    No worries. Enter your email address and we’ll send you a password reset link.
                </p>

                @if (session('status'))
                    <div class="rex-login-success-alert" role="alert">
                        <span class="rex-login-success-alert-icon" aria-hidden="true">✓</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="rex-login-form rex-forgot-password-form">
                    @csrf

                    <div class="rex-login-field-group">
                        <label for="email" class="rex-login-label">
                            Email Address
                        </label>

                        <div class="rex-login-input-wrap">
                            <span class="rex-login-input-icon" aria-hidden="true">
                                <svg class="rex-login-icon">
                                    <use href="#rex-icon-mail"/>
                                </svg>
                            </span>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="Enter your email address"
                                class="rex-login-input @error('email') rex-login-input-error @enderror"
                                @error('email')
                                    aria-invalid="true"
                                    aria-describedby="rex-email-error"
                                @enderror
                            >
                        </div>

                        @error('email')
                            <p id="rex-email-error" class="rex-login-error" role="alert">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="rex-login-submit"
                        data-test="email-password-reset-link-button"
                    >
                        <svg class="rex-login-icon" aria-hidden="true">
                            <use href="#rex-icon-mail"/>
                        </svg>
                        <span>Send Reset Link</span>
                    </button>
                </form>

                <div class="rex-login-back-link-wrap">
                    <a href="{{ route('login') }}" class="rex-login-back-link">
                        ← Back to Sign In
                    </a>
                </div>
            </section>

            <footer class="rex-login-footer">
                <p>© {{ now()->year }} Rexxsol. All rights reserved.</p>

                <div class="rex-login-footer-links">
                    <span>Privacy Policy</span>
                    <span class="rex-login-footer-separator" aria-hidden="true">|</span>
                    <span>Terms of Service</span>
                </div>
            </footer>
        </main>
    </div>
</body>
</html>
