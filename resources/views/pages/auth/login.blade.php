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

    {{-- Shared decorative icons --}}
    <svg xmlns="http://www.w3.org/2000/svg"
         width="0"
         height="0"
         aria-hidden="true"
         focusable="false"
         style="position:absolute;overflow:hidden">
        <defs>
            <symbol id="rex-icon-document" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                <path d="M14 2v6h6M8 13h8M8 17h8"/>
            </symbol>

            <symbol id="rex-icon-chart" viewBox="0 0 24 24">
                <path d="M3 3v18h18M7 17v-5M12 17V8M17 17V5"/>
                <path d="m6 8 5-4 5 1 4-3"/>
            </symbol>

            <symbol id="rex-icon-shield" viewBox="0 0 24 24">
                <path d="M12 3 3 7v5c0 5 5 8 9 10 4-2 9-5 9-10V7l-9-4Z"/>
                <path d="m8 12 3 3 5-6"/>
            </symbol>

            <symbol id="rex-icon-mail" viewBox="0 0 24 24">
                <rect x="3" y="5" width="18" height="14" rx="2"/>
                <path d="m3 6 9 7 9-7"/>
            </symbol>

            <symbol id="rex-icon-lock" viewBox="0 0 24 24">
                <rect x="5" y="10" width="14" height="11" rx="2"/>
                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
            </symbol>

            <symbol id="rex-icon-eye" viewBox="0 0 24 24">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                <circle cx="12" cy="12" r="3"/>
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
            <section class="rex-login-card" aria-labelledby="rex-login-title">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Rexxsol"
                    class="rex-login-card-logo"
                    decoding="async"
                >

                <h2 id="rex-login-title" class="rex-login-card-title">
                    Welcome Back
                </h2>

                <p class="rex-login-card-subtitle">
                    Sign in to your Rexxsol CMS
                </p>

                <x-auth-session-status
                    class="rex-login-session-status text-center"
                    :status="session('status')"
                />

                <form
                    method="POST"
                    action="{{ route('login.store') }}"
                    class="rex-login-form"
                >
                    @csrf

                    {{-- Field errors appear below their inputs; preserve non-field errors here. --}}
                    @php
                        $nonFieldErrors = collect($errors->keys())
                            ->reject(fn ($key) => in_array($key, ['email', 'password'], true))
                            ->values()
                            ->all();
                    @endphp

                    @if (! empty($nonFieldErrors))
                        <div class="rex-login-form-alert" role="alert">
                            @foreach ($nonFieldErrors as $key)
                                @foreach ($errors->get($key, []) as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            @endforeach
                        </div>
                    @endif

                    <div class="rex-login-field-group">
                        <label for="email" class="rex-login-label">
                            Email / Username
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
                                placeholder="Enter your email or username"
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

                    <div class="rex-login-field-group">
                        <div class="rex-login-label-row">
                            <label for="password" class="rex-login-label">
                                Password
                            </label>
                        </div>

                        <div class="rex-login-input-wrap">
                            <span class="rex-login-input-icon" aria-hidden="true">
                                <svg class="rex-login-icon">
                                    <use href="#rex-icon-lock"/>
                                </svg>
                            </span>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="rex-login-input rex-login-input--password @error('password') rex-login-input-error @enderror"
                                @error('password')
                                    aria-invalid="true"
                                    aria-describedby="rex-password-error"
                                @enderror
                            >

                            <button
                                type="button"
                                class="rex-login-password-toggle"
                                aria-label="Show password"
                                aria-controls="password"
                                aria-pressed="false"
                                onclick="
                                    const input = this.parentElement.querySelector('input');
                                    const visible = input.type === 'password';
                                    input.type = visible ? 'text' : 'password';
                                    this.setAttribute('aria-pressed', String(visible));
                                    this.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
                                "
                            >
                                <svg class="rex-login-icon" aria-hidden="true">
                                    <use href="#rex-icon-eye"/>
                                    <path class="rex-login-eye-slash" d="m3 3 18 18"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <p id="rex-password-error" class="rex-login-error" role="alert">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="rex-login-options">
                        <label class="rex-login-remember" for="remember">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span>Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="rex-login-link">
                                Forgot your password?
                            </a>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="rex-login-submit"
                        data-test="login-button"
                    >
                        <svg class="rex-login-icon" aria-hidden="true">
                            <use href="#rex-icon-login"/>
                        </svg>
                        <span>Sign in</span>
                    </button>
                </form>

            </section>

            {{-- Outside the card, as in the reference. --}}
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

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>