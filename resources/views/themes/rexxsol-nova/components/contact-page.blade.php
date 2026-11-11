@php
    $heroEyebrow = trim((string) ($page?->hero_eyebrow ?? '')) ?: 'Let’s build something meaningful';
    $heroHeading = trim((string) ($page?->hero_heading ?? '')) ?: 'Tell us what you have in mind.';
    $heroDescription = trim((string) ($page?->hero_description ?? '')) ?: 'Share a little about your project and our team will be in touch.';
    $infoHeading = trim((string) ($page?->contact_info_heading ?? '')) ?: 'Let’s start a conversation.';
    $infoDescription = trim((string) ($page?->contact_info_description ?? '')) ?: 'Whether you have a clear brief or an early idea, we’d love to hear it.';
    $formHeading = trim((string) ($page?->contact_form_heading ?? '')) ?: 'Tell us about your project';
    $formDescription = trim((string) ($page?->contact_form_description ?? '')) ?: 'A few details to help us understand what you need.';
@endphp

<div class="nova-contact-page">
    <section class="nova-page nova-inner-hero nova-contact-hero" aria-labelledby="nova-contact-title">
        <div class="nova-container">
        <header class="nova-page-heading">
            <p class="nova-page-kicker">{{ $heroEyebrow }}</p>
            <h1 id="nova-contact-title">{{ $heroHeading }}</h1>
            <p>{{ $heroDescription }}</p>
        </header>
        </div>
    </section>

    <section class="nova-section nova-contact-content" aria-label="Contact information and enquiry form">
        <div class="nova-container">
        <div class="nova-contact-layout">
            <aside class="nova-contact-info" aria-labelledby="nova-contact-info-title">
                <h2 id="nova-contact-info-title">{{ $infoHeading }}</h2>
                <p class="nova-contact-info-intro">{{ $infoDescription }}</p>

                <div class="nova-contact-rows">
                    <a class="nova-contact-row" href="mailto:{{ $contactEmail }}">
                        <span class="nova-contact-icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                        <span class="nova-contact-row-copy"><strong>Email us</strong><span>{{ $contactEmail }}</span></span>
                    </a>
                    <a class="nova-contact-row" href="tel:{{ preg_replace('/[^+\d]/', '', $contactPhone) }}">
                        <span class="nova-contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                        <span class="nova-contact-row-copy"><strong>Call us</strong><span>{{ $contactPhone }}</span></span>
                    </a>
                    <div class="nova-contact-row">
                        <span class="nova-contact-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                        <span class="nova-contact-row-copy"><strong>Visit us</strong><span>{{ $contactAddress }}</span></span>
                    </div>
                </div>

                <div class="nova-contact-office">
                    <h3>Find our office.</h3>
                    @if ($officeMapEmbedUrl)
                        <div class="nova-contact-map">
                            <iframe
                                src="{{ $officeMapEmbedUrl }}"
                                title="Map showing Rexxsol’s verified office location"
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @else
                        <p class="nova-contact-map-unconfigured">An office map hasn’t been configured yet. Please use the address above.</p>
                    @endif
                    @if ($officeMapDirectionsUrl)
                        <a class="nova-contact-directions" href="{{ $officeMapDirectionsUrl }}" target="_blank" rel="noopener noreferrer">
                            Get directions <span aria-hidden="true">↗</span>
                        </a>
                    @endif
                </div>
            </aside>

            <form class="nova-contact-form" method="POST" action="{{ route('website.contact.store') }}" data-contact-form aria-labelledby="nova-contact-form-title">
                @csrf
                @if (session('success'))
                    <p class="nova-form-success" role="status" tabindex="-1">{{ session('success') }}</p>
                @endif
                @if ($errors->any())
                    <p class="nova-form-errors" role="alert">Please check the highlighted fields and try again.</p>
                @endif
                <header class="nova-contact-form-heading">
                    <h2 id="nova-contact-form-title">{{ $formHeading }}</h2>
                    <p>{{ $formDescription }}</p>
                </header>

                <div class="nova-form-row">
                    <label for="contact-name"><span class="nova-field-label">Name <span aria-hidden="true">*</span></span>
                        <input id="contact-name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror placeholder="Your name">
                        @error('name')<small id="contact-name-error" class="nova-field-error">{{ $message }}</small>@enderror
                    </label>
                    <label for="contact-email"><span class="nova-field-label">Email <span aria-hidden="true">*</span></span>
                        <input id="contact-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror placeholder="you@company.com">
                        @error('email')<small id="contact-email-error" class="nova-field-error">{{ $message }}</small>@enderror
                    </label>
                </div>

                <div class="nova-form-row">
                    <label for="contact-company"><span class="nova-field-label">Company <span>(Optional)</span></span>
                        <input id="contact-company" name="company" value="{{ old('company') }}" autocomplete="organization" maxlength="255" @error('company') aria-invalid="true" aria-describedby="contact-company-error" @enderror placeholder="Company name">
                        @error('company')<small id="contact-company-error" class="nova-field-error">{{ $message }}</small>@enderror
                    </label>
                    <label for="contact-phone"><span class="nova-field-label">Phone</span>
                        <input id="contact-phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" pattern="\+?[0-9().\s-]{7,50}" maxlength="50" data-phone-number-only @error('phone') aria-invalid="true" aria-describedby="contact-phone-error" @enderror placeholder="+92">
                        @error('phone')<small id="contact-phone-error" class="nova-field-error">{{ $message }}</small>@enderror
                    </label>
                </div>

                <label for="contact-service"><span class="nova-field-label">What can we help with? <span aria-hidden="true">*</span></span>
                    <select id="contact-service" name="service" required @error('service') aria-invalid="true" aria-describedby="contact-service-error" @enderror>
                        <option value="">Choose a service</option>
                        @foreach (($contactServices ?? []) as $service)
                            <option value="{{ $service }}" @selected(old('service') === $service)>{{ $service }}</option>
                        @endforeach
                    </select>
                    @error('service')<small id="contact-service-error" class="nova-field-error">{{ $message }}</small>@enderror
                </label>

                <label for="contact-message"><span class="nova-field-label">Project details <span aria-hidden="true">*</span></span>
                    <textarea id="contact-message" name="message" rows="5" minlength="10" maxlength="4000" required @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror placeholder="Tell us about your goals, requirements or ideas...">{{ old('message') }}</textarea>
                    @error('message')<small id="contact-message-error" class="nova-field-error">{{ $message }}</small>@enderror
                </label>
                <p class="nova-contact-form-note">Please avoid sharing passwords or sensitive information.</p>

                <label class="nova-honeypot" aria-hidden="true">Website URL<input name="website_url" tabindex="-1" autocomplete="off"></label>
                <button class="nova-button nova-button-primary" type="submit" data-contact-submit>
                    <span data-contact-submit-label>Send enquiry</span>
                    <i class="fa-solid fa-paper-plane" aria-hidden="true" data-contact-submit-icon></i>
                    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" data-contact-submit-spinner hidden></i>
                </button>
                @if ($privacyPolicyLink)
                    <p class="nova-contact-privacy">
                        By submitting, you agree to our
                        <a href="{{ $privacyPolicyLink['url'] }}" @if (($privacyPolicyLink['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>Privacy Policy</a>.
                    </p>
                @else
                    <p class="nova-contact-privacy">By submitting, you agree to our privacy practices. A Privacy Policy page has not been configured yet.</p>
                @endif
            </form>
        </div>
        </div>
    </section>
</div>
