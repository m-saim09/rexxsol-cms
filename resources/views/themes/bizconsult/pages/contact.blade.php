@extends('themes.bizconsult.layouts.app')

@push('styles')
<style>
	.rx-contact-page{overflow:hidden;color:#526477;background:#fff}
	.rx-contact-container{width:min(1180px,calc(100% - 40px));margin:0 auto}
	.rx-contact-hero{padding:7.5rem 0 4.25rem;background:#f3f7fa}
	.rx-contact-hero-grid{max-width:800px}
	.rx-contact-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:16px;color:#008f71;font-size:.76rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
	.rx-contact-eyebrow:before{width:27px;height:2px;background:#00b98e;content:''}
	.rx-contact-hero h1,.rx-contact-section h2,.rx-contact-next h2,.rx-contact-faq h2{margin:0;color:#103354;letter-spacing:-.045em}
	.rx-contact-hero h1{max-width:740px;font-size:clamp(2.65rem,4vw,3.85rem);line-height:1.04}
	.rx-contact-hero p{max-width:760px;margin:18px 0 0;color:#526477;font-size:1.06rem;line-height:1.72}
	.rx-contact-reassurance{display:flex;align-items:flex-start;gap:13px;padding:17px 0 17px 19px;border-left:2px solid #00b98e;color:#103354;font-size:1rem;font-weight:650;line-height:1.6}
	.rx-contact-reassurance i{margin-top:4px;color:#008f71}
	.rx-contact-section{padding:88px 0 96px}
	.rx-contact-layout{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(270px,.75fr);gap:76px;align-items:start}
	.rx-contact-form-panel{padding:34px;border:1px solid #e0e8ed;border-radius:14px;background:#fff;box-shadow:0 12px 28px rgba(16,51,84,.045)}
	.rx-contact-form-panel h2,.rx-contact-details h2{font-size:clamp(1.75rem,2.5vw,2.2rem);line-height:1.14}
	.rx-contact-form-intro,.rx-contact-details-intro{margin:12px 0 26px;color:#526477;font-size:1rem;line-height:1.7}
	.rx-contact-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px 18px}
	.rx-contact-field{min-width:0}
	.rx-contact-field--full{grid-column:1/-1}
	.rx-contact-field label{display:block;margin-bottom:8px;color:#203c56;font-size:.94rem;font-weight:650}
	.rx-contact-field input,.rx-contact-field select,.rx-contact-field textarea{display:block;width:100%;min-height:50px;padding:12px 14px;border:1px solid #d7e1e7;border-radius:8px;background:#fff;color:#183650;font:inherit;font-size:.96rem;line-height:1.5;transition:border-color 180ms ease,box-shadow 180ms ease}
	.rx-contact-field textarea{min-height:148px;resize:vertical}
	.rx-contact-field input::placeholder,.rx-contact-field textarea::placeholder{color:#8192a0;opacity:1}
	.rx-contact-field input:focus,.rx-contact-field select:focus,.rx-contact-field textarea:focus{border-color:#00a47c;outline:0;box-shadow:0 0 0 3px rgba(0,185,142,.15)}
	.rx-contact-field [aria-invalid="true"]{border-color:#bd4b53}
	.rx-contact-error{display:block;margin-top:6px;color:#a9323a;font-size:.88rem;line-height:1.4}
	.rx-contact-submit{display:inline-flex;min-height:48px;align-items:center;justify-content:center;gap:10px;padding:12px 20px;border:1px solid #00b98e;border-radius:999px;background:#00b98e;color:#fff;font:inherit;font-weight:700;cursor:pointer;transition:transform 200ms ease,background-color 200ms ease,border-color 200ms ease}
	.rx-contact-submit:hover,.rx-contact-submit:focus-visible{transform:translateY(-1px);border-color:#0b2f3a;background:#0b2f3a;color:#fff}
	.rx-contact-submit:disabled{cursor:wait;opacity:.72;transform:none}
	.rx-contact-privacy{margin:13px 0 0;color:#6c7d8c;font-size:.88rem;line-height:1.5}
	.rx-contact-details{padding-top:8px}
	.rx-contact-details h2{margin:0;color:#103354;letter-spacing:-.04em}
	.rx-contact-details-intro{margin-bottom:22px}
	.rx-contact-detail-list{display:grid;gap:0;border-top:1px solid #dfe7ec}
	.rx-contact-detail{display:grid;grid-template-columns:42px minmax(0,1fr);gap:13px;align-items:start;padding:18px 0;border-bottom:1px solid #dfe7ec}
	.rx-contact-detail-icon{display:flex;width:38px;height:38px;align-items:center;justify-content:center;border:1px solid #d9eee7;border-radius:10px;background:#f1faf6;color:#008f71}
	.rx-contact-detail a.rx-contact-detail-icon{color:#008f71;text-decoration:none}
	.rx-contact-detail a.rx-contact-detail-icon:hover{background:#e6f6ef;color:#103354}
	.rx-contact-detail a.rx-contact-detail-icon:focus-visible{outline:2px solid #00a47c;outline-offset:3px}
	.rx-contact-detail-label{display:block;margin:0 0 4px;color:#718292;font-size:.84rem;font-weight:650}
	.rx-contact-detail a,.rx-contact-detail-value{color:#103354;font-weight:600;line-height:1.55;text-decoration:none;overflow-wrap:anywhere}
	.rx-contact-detail a:hover{color:#007f69;text-decoration:underline}
	.rx-contact-no-details{margin-top:24px;padding:17px 18px;border-left:2px solid #00b98e;background:#f5f8fa;color:#526477;line-height:1.65}
	.rx-contact-location{margin-top:28px}
	.rx-contact-location-head{margin-bottom:13px}
	.rx-contact-map{overflow:hidden;border:1px solid #dce6ec;border-radius:10px;background:#fff}
	.rx-contact-map iframe{display:block;width:100%;height:220px;border:0}
	.rx-contact-location-meta{display:flex;align-items:flex-start;flex-direction:column;gap:10px;padding:13px 14px;border-top:1px solid #dce6ec}
	.rx-contact-location-meta strong{display:block;margin-bottom:3px;color:#103354}
	.rx-contact-location-meta address{margin:0;color:#526477;font-style:normal;line-height:1.5}
	.rx-contact-directions{display:inline-flex;flex:0 0 auto;align-items:center;gap:8px;color:#007f69;font-weight:700;text-decoration:none}
	.rx-contact-directions:hover,.rx-contact-directions:focus-visible{color:#103354}
	.rx-contact-next{padding:80px 0;background:#f5f8fb}
	.rx-contact-section-heading{margin-bottom:30px}
	.rx-contact-section-heading h2,.rx-contact-faq h2{font-size:clamp(1.9rem,3vw,2.55rem);line-height:1.12}
	.rx-contact-steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));border-top:1px solid #d8e3e9;border-bottom:1px solid #d8e3e9}
	.rx-contact-step{padding:23px 26px 24px 0}
	.rx-contact-step+.rx-contact-step{padding-left:26px;border-left:1px solid #d8e3e9}
	.rx-contact-step-number{display:block;margin-bottom:12px;color:#008f71;font-size:.79rem;font-weight:800;letter-spacing:.13em}
	.rx-contact-step h3{margin:0 0 8px;color:#103354;font-size:1.08rem;line-height:1.4}
	.rx-contact-step p{margin:0;color:#526477;line-height:1.65}
	.rx-contact-faq{padding:82px 0 96px}
	.rx-contact-faq-grid{display:grid;grid-template-columns:.78fr 1.22fr;gap:60px;align-items:start}
	.rx-contact-faq h2{margin:0;color:#103354;letter-spacing:-.04em}
	.rx-contact-faq-list{border-top:1px solid #dce6ec}
	.rx-contact-faq-list details{border-bottom:1px solid #dce6ec}
	.rx-contact-faq-list summary{position:relative;padding:19px 42px 19px 0;color:#103354;font-weight:650;line-height:1.5;cursor:pointer;list-style:none}
	.rx-contact-faq-list summary::-webkit-details-marker{display:none}
	.rx-contact-faq-list summary:after{position:absolute;top:50%;right:4px;color:#008f71;font-size:1.25rem;content:'+';transform:translateY(-50%)}
	.rx-contact-faq-list details[open] summary:after{content:'−'}
	.rx-contact-faq-list summary:focus-visible{outline:2px solid #00a47c;outline-offset:3px}
	.rx-contact-faq-list p{margin:0;padding:0 35px 19px 0;color:#526477;line-height:1.7}
	.rx-contact-honeypot{position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden}
	.rx-contact-success{padding:30px;border:1px solid #b8e4d5;border-radius:12px;background:#f1faf6;color:#103354}
	.rx-contact-success-icon{display:flex;width:44px;height:44px;align-items:center;justify-content:center;margin-bottom:16px;border-radius:50%;background:#dff5ec;color:#008f71}
	.rx-contact-success h2{margin:0 0 10px;color:#103354;font-size:1.65rem}
	.rx-contact-success p{margin:0 0 18px;line-height:1.65}
	.rx-contact-success a{color:#007f69;font-weight:700;text-decoration:none}
	@media(max-width:991.98px){.rx-contact-hero{padding:7rem 0 3.5rem}.rx-contact-layout{grid-template-columns:1fr;gap:38px}.rx-contact-form-panel{padding:28px}.rx-contact-faq-grid{gap:34px}}
	@media(max-width:767.98px){.rx-contact-container{width:min(calc(100% - 32px),560px)}.rx-contact-hero{padding:6.5rem 0 3rem}.rx-contact-hero h1{font-size:clamp(2.35rem,10vw,3.15rem)}.rx-contact-hero p{font-size:1rem}.rx-contact-section{padding:56px 0 68px}.rx-contact-layout{grid-template-columns:1fr;gap:42px}.rx-contact-form-panel{padding:23px 18px}.rx-contact-form-grid{grid-template-columns:1fr;gap:17px}.rx-contact-field--full{grid-column:auto}.rx-contact-submit{width:100%}.rx-contact-map iframe{height:310px}.rx-contact-next{padding:60px 0}.rx-contact-steps{grid-template-columns:1fr}.rx-contact-step,.rx-contact-step+.rx-contact-step{padding:20px 0;border-left:0}.rx-contact-step+.rx-contact-step{border-top:1px solid #d8e3e9}.rx-contact-faq{padding:60px 0 72px}.rx-contact-faq-grid{grid-template-columns:1fr;gap:22px}}
	@media(prefers-reduced-motion:reduce){.rx-contact-field input,.rx-contact-field select,.rx-contact-field textarea,.rx-contact-submit{transition:none!important}}
</style>
@endpush

@section('content')
<div class="rx-contact-page">
	<section class="rx-contact-hero">
		<div class="rx-contact-container rx-contact-hero-grid">
			<div>
				<div class="rx-contact-eyebrow">{{ $page?->hero_eyebrow ?: 'Contact Rexxsol' }}</div>
				<h1>{{ $page?->hero_heading ?: 'Let’s talk about what you’re building.' }}</h1>
				<p>{{ $page?->hero_description ?: 'Tell us what you need — we’ll take it from there.' }}</p>
			</div>
		</div>
	</section>

	<section class="rx-contact-section">
		<div class="rx-contact-container rx-contact-layout">
			<div class="rx-contact-form-panel">
				@if (session('success'))
					<div class="rx-contact-success" role="status" aria-live="polite">
						<span class="rx-contact-success-icon"><i class="fa fa-check" aria-hidden="true"></i></span>
						<h2>Thanks for reaching out.</h2>
						<p>{{ session('success') }}</p>
						<a href="{{ route('home') }}">Back to homepage <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
					</div>
				@else
					<div class="rx-contact-eyebrow">Start a conversation</div>
					<h2>Tell us about your project.</h2>
					<p class="rx-contact-form-intro">Share a few details and we’ll get back to you with the right next step.</p>
					<form method="POST" action="{{ route('website.contact.store') }}" data-contact-form>
						@csrf
						<div class="rx-contact-honeypot" aria-hidden="true"><label for="website_url">Leave this field empty</label><input id="website_url" name="website_url" type="text" tabindex="-1" aria-hidden="true" autocomplete="off"></div>
						<div class="rx-contact-form-grid">
							<div class="rx-contact-field">
								<label for="contact-name">Full Name <span aria-hidden="true">*</span></label>
								<input id="contact-name" name="name" type="text" autocomplete="name" value="{{ old('name') }}" required maxlength="255" @if ($errors->has('name')) aria-invalid="true" aria-describedby="contact-name-error" @endif>
								@error('name')<span class="rx-contact-error" id="contact-name-error">{{ $message }}</span>@enderror
							</div>
							<div class="rx-contact-field">
								<label for="contact-email">Email Address <span aria-hidden="true">*</span></label>
								<input id="contact-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required maxlength="255" @if ($errors->has('email')) aria-invalid="true" aria-describedby="contact-email-error" @endif>
								@error('email')<span class="rx-contact-error" id="contact-email-error">{{ $message }}</span>@enderror
							</div>
							<div class="rx-contact-field">
								<label for="contact-phone">Phone Number <span>(optional)</span></label>
								<input id="contact-phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}" maxlength="50" @if ($errors->has('phone')) aria-invalid="true" aria-describedby="contact-phone-error" @endif>
								@error('phone')<span class="rx-contact-error" id="contact-phone-error">{{ $message }}</span>@enderror
							</div>
							<div class="rx-contact-field">
								<label for="contact-company">Company / Organization <span>(optional)</span></label>
								<input id="contact-company" name="company" type="text" autocomplete="organization" value="{{ old('company') }}" maxlength="255" @if ($errors->has('company')) aria-invalid="true" aria-describedby="contact-company-error" @endif>
								@error('company')<span class="rx-contact-error" id="contact-company-error">{{ $message }}</span>@enderror
							</div>
							<div class="rx-contact-field rx-contact-field--full">
								<label for="contact-subject">Subject</label>
								<input id="contact-subject" name="subject" type="text" value="{{ old('subject') }}" maxlength="255" @if ($errors->has('subject')) aria-invalid="true" aria-describedby="contact-subject-error" @endif>
								@error('subject')<span class="rx-contact-error" id="contact-subject-error">{{ $message }}</span>@enderror
							</div>
							<div class="rx-contact-field rx-contact-field--full">
								<label for="contact-service">Service Interested In</label>
								<select id="contact-service" name="service" required @if ($errors->has('service')) aria-invalid="true" aria-describedby="contact-service-error" @endif>
									<option value="" disabled @selected(! old('service'))>Choose a service</option>
									@foreach ($contactServices as $service)
										<option value="{{ $service }}" @selected(old('service') === $service)>{{ $service }}</option>
									@endforeach
								</select>
								@error('service')<span class="rx-contact-error" id="contact-service-error">{{ $message }}</span>@enderror
							</div>
							<div class="rx-contact-field rx-contact-field--full">
								<label for="contact-message">Message / Tell us about your project <span aria-hidden="true">*</span></label>
								<textarea id="contact-message" name="message" rows="5" required minlength="10" maxlength="4000" placeholder="Tell us a little about your goals, needs or current challenge." @if ($errors->has('message')) aria-invalid="true" aria-describedby="contact-message-error" @endif>{{ old('message') }}</textarea>
								@error('message')<span class="rx-contact-error" id="contact-message-error">{{ $message }}</span>@enderror
							</div>
							<div class="rx-contact-field rx-contact-field--full">
								<button class="rx-contact-submit" type="submit">Send project inquiry <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
								<p class="rx-contact-privacy">Your information will only be used to respond to your inquiry.</p>
							</div>
						</div>
					</form>
				@endif
			</div>

			<aside class="rx-contact-details" aria-labelledby="contact-direct-heading">
				<div class="rx-contact-eyebrow">Direct contact</div>
				<h2 id="contact-direct-heading">Prefer to reach us directly?</h2>
				<p class="rx-contact-details-intro">Connect with Rexxsol through the channel that works best for you.</p>
				@if (count($contactDetails))
					<div class="rx-contact-detail-list">
						@foreach ($contactDetails as $detail)
							<div class="rx-contact-detail">
								@if ($detail['href'])
									<a class="rx-contact-detail-icon" href="{{ $detail['href'] }}" aria-label="{{ $detail['label'] }}" title="{{ $detail['label'] }}" @if (($detail['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif><i class="fa {{ $detail['icon'] }}" aria-hidden="true"></i></a>
								@else
									<span class="rx-contact-detail-icon"><i class="fa {{ $detail['icon'] }}" aria-hidden="true"></i></span>
								@endif
								<div><span class="rx-contact-detail-label">{{ $detail['label'] }}</span>@if ($detail['href'])<a href="{{ $detail['href'] }}" @if (($detail['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>{{ $detail['value'] }}</a>@else<span class="rx-contact-detail-value">{{ $detail['value'] }}</span>@endif</div>
							</div>
						@endforeach
					</div>
				@else
					<p class="rx-contact-no-details">Send us a note through the inquiry form and we’ll make sure it reaches the right team.</p>
				@endif
				<div class="rx-contact-location">
					<div class="rx-contact-location-head"><div class="rx-contact-eyebrow">Our location</div></div>
					<div class="rx-contact-map">
						<iframe src="https://maps.google.com/maps?q={{ urlencode($officeAddress) }}&amp;output=embed" title="Rexxsol office location in Rahim Yar Khan" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
						<div class="rx-contact-location-meta">
							<div><strong>Rexxsol</strong><address>{{ $officeAddress }}</address></div>
							<a class="rx-contact-directions" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($officeAddress) }}" target="_blank" rel="noopener noreferrer">Get directions <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
						</div>
					</div>
				</div>
			</aside>
		</div>
	</section>

	<section class="rx-contact-next">
		<div class="rx-contact-container">
			<div class="rx-contact-section-heading"><div class="rx-contact-eyebrow">What happens next</div><h2>A straightforward way to get started.</h2></div>
			<div class="rx-contact-steps">
				@foreach ([['We review your inquiry', 'We look at your goals, requirements and the type of support you need.'], ['We get in touch', 'A member of the Rexxsol team follows up to understand the project in more detail.'], ['We define the next step', 'Together, we identify the most practical way to move the project forward.']] as $index => $step)
					<article class="rx-contact-step"><span class="rx-contact-step-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>
				@endforeach
			</div>
		</div>
	</section>

	<section class="rx-contact-faq">
		<div class="rx-contact-container rx-contact-faq-grid">
			<div><div class="rx-contact-eyebrow">Quick questions</div><h2>A few things you might be wondering.</h2></div>
			<div class="rx-contact-faq-list">
				<details><summary>What types of projects does Rexxsol work on?</summary><p>We work on web and mobile products, custom software, integrations, automation, and the ongoing improvement of digital systems.</p></details>
				<details><summary>Can Rexxsol improve an existing website or software system?</summary><p>Yes. Tell us what you have today and what you would like to improve, and we can discuss practical options.</p></details>
				<details><summary>Do you provide support after a project goes live?</summary><p>Yes. Support and maintenance can help keep systems dependable and aligned with changing business needs.</p></details>
				<details><summary>I’m not sure which service I need — can I still get in touch?</summary><p>Of course. Choose “Not Sure Yet” in the form and describe the challenge you are working through.</p></details>
			</div>
		</div>
	</section>
</div>

<script>
	document.querySelectorAll('[data-contact-form]').forEach(function (form) {
		form.addEventListener('submit', function () {
			const submitButton = form.querySelector('button[type="submit"]');
			if (submitButton) {
				submitButton.disabled = true;
				submitButton.setAttribute('aria-busy', 'true');
			}
		});
	});
	document.querySelectorAll('.rx-contact-faq-list details').forEach(function (item) {
		let openedByHover = false;

		item.addEventListener('pointerenter', function (event) {
			if (event.pointerType === 'mouse') {
				item.open = true;
				openedByHover = true;
			}
		});

		item.addEventListener('pointerleave', function (event) {
			if (event.pointerType === 'mouse' && openedByHover) {
				item.open = false;
				openedByHover = false;
			}
		});
	});
</script>
@endsection