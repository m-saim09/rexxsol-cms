@extends('themes.bizconsult.layouts.app')

@php
	$withPreview = fn (string $url): string => ($previewTheme ?? null)
		? $url . (str_contains($url, '?') ? '&' : '?') . 'theme=' . urlencode($previewTheme)
		: $url;
	$aboutIllustration = $page?->featured_image
		? \Illuminate\Support\Facades\Storage::disk('public')->url($page->featured_image)
		: asset('assets-bizconsult/img/about.png');
@endphp

@push('styles')
<style>
	.rx-about-page{overflow:hidden;color:#526477;background:#fff}
	.rx-about-container{width:min(1180px,calc(100% - 40px));margin:0 auto}
	.rx-about-hero{position:relative;padding:8.5rem 0 6rem;background:#f3f7fa}
	.rx-about-hero:after{position:absolute;right:-10%;bottom:-1px;left:-10%;height:74px;content:'';background:#fff;clip-path:ellipse(62% 54% at 50% 100%)}
	.rx-about-hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:1.04fr .96fr;align-items:center;gap:56px}
	.rx-about-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:18px;color:#008f71;font-size:.76rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
	.rx-about-eyebrow:before{width:28px;height:2px;content:'';background:#00b98e}
	.rx-about-hero h1,.rx-about-section-head h2,.rx-about-story h2,.rx-about-mission h2{color:#103354;letter-spacing:-.045em}
	.rx-about-hero h1{max-width:720px;margin:0 0 22px;font-size:clamp(2.65rem,4.1vw,4.1rem);line-height:1.04}
	.rx-about-hero-copy{max-width:590px;margin:0 0 22px;color:#526477;font-size:1.06rem;line-height:1.75}
	.rx-about-experience{display:flex;align-items:center;gap:10px;margin:0 0 28px;color:#103354;font-size:.94rem;font-weight:700}
	.rx-about-experience i{color:#00a47c}
	.rx-about-primary-link,.rx-about-text-link{display:inline-flex;align-items:center;gap:10px;font-weight:700;text-decoration:none}
	.rx-about-primary-link{min-height:50px;padding:13px 23px;border:1px solid #00b98e;border-radius:999px;background:#00b98e;color:#fff;transition:transform 240ms ease,background-color 240ms ease}
	.rx-about-primary-link:hover,.rx-about-primary-link:focus-visible{transform:translateY(-2px);background:#0b2f3a;color:#fff}
	.rx-about-arrow{transition:transform 240ms ease}.rx-about-primary-link:hover .rx-about-arrow,.rx-about-text-link:hover .rx-about-arrow{transform:translateX(4px)}
	.rx-about-hero-visual{position:relative;display:flex;min-height:420px;align-items:center;justify-content:center}
	.rx-about-hero-visual:before{position:absolute;top:8%;right:4%;width:75%;height:75%;border-radius:50%;background:#d9f3e9;content:''}
	.rx-about-hero-visual img{position:relative;z-index:1;display:block;width:100%;max-height:440px;object-fit:contain;filter:drop-shadow(0 20px 26px rgba(16,51,84,.11))}
	.rx-about-section{padding:100px 0}
	.rx-about-section--soft{background:#f5f8fb}
	.rx-about-section-head{display:grid;grid-template-columns:1.12fr .88fr;align-items:end;gap:24px 36px;margin-bottom:38px}
	.rx-about-section-head h2,.rx-about-story h2,.rx-about-mission h2{margin:0;font-size:clamp(2.1rem,3.2vw,3rem);line-height:1.08}
	.rx-about-section-head p,.rx-about-story p{max-width:570px;margin:0;color:#526477;font-size:1.02rem;line-height:1.75}
	.rx-about-who{padding-top:108px;padding-bottom:108px}
	.rx-about-who-grid{display:grid;grid-template-columns:.82fr 1.18fr;gap:78px;align-items:start}
	.rx-about-who-heading h2{max-width:440px;margin:0;color:#103354;font-size:clamp(2.2rem,3.25vw,3.05rem);line-height:1.08;letter-spacing:-.045em}
	.rx-about-who-heading .rx-about-eyebrow{margin-bottom:20px}
	.rx-about-who-copy{max-width:690px;padding-top:37px}
	.rx-about-who-lead{margin:0 0 25px;padding-left:20px;border-left:2px solid #00b98e;color:#103354;font-size:1.24rem;font-weight:600;line-height:1.6}
	.rx-about-story-copy{display:grid;grid-template-columns:1fr 1fr;gap:30px}
	.rx-about-story-copy p{max-width:unset;margin:0;color:#526477;font-size:1rem;line-height:1.8}
	.rx-about-highlights{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-top:1px solid #dce6ec;border-bottom:1px solid #dce6ec}
	.rx-about-highlight{display:flex;min-height:116px;flex-direction:column;justify-content:center;padding:24px 22px}
	.rx-about-highlight+.rx-about-highlight{border-left:1px solid #dce6ec}
	.rx-about-highlight strong{display:block;margin-bottom:6px;color:#103354;font-size:1.08rem;line-height:1.3}
	.rx-about-highlight span{color:#617487;font-size:.93rem;line-height:1.5}
	.rx-about-mission-grid{display:grid;grid-template-columns:1fr 1fr;gap:0;border-top:1px solid #dce6ec;border-bottom:1px solid #dce6ec}
	.rx-about-mission{padding:34px 42px 36px 0}
	.rx-about-mission+.rx-about-mission{padding-right:0;padding-left:42px;border-left:1px solid #dce6ec}
	.rx-about-mission-mark{display:flex;width:44px;height:44px;align-items:center;justify-content:center;margin-bottom:20px;border:1px solid rgba(0,185,142,.24);border-radius:12px;background:#edf9f5;color:#008f71}
	.rx-about-mission h2{margin:0 0 13px;font-size:1.55rem;line-height:1.25}
	.rx-about-mission p{max-width:500px;margin:0;color:#526477;font-size:1rem;line-height:1.75}
	.rx-about-values{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-top:1px solid #dce6ec;border-bottom:1px solid #dce6ec}
	.rx-about-value{padding:28px 22px 30px}
	.rx-about-value:first-child{padding-left:0}
	.rx-about-value+.rx-about-value{border-left:1px solid #dce6ec}
	.rx-about-value-icon{display:flex;width:42px;height:42px;align-items:center;justify-content:center;margin-bottom:18px;color:#008f71;font-size:1.15rem}
	.rx-about-value h3{margin:0 0 10px;color:#103354;font-size:1.18rem;line-height:1.35}
	.rx-about-value p{margin:0;color:#526477;line-height:1.7}
	.rx-about-capabilities{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-top:1px solid #dce6ec}
	.rx-about-capability{display:flex;min-height:76px;align-items:center;gap:12px;padding:18px 16px;border-bottom:1px solid #dce6ec;color:#103354;font-weight:650;text-decoration:none;transition:color 220ms ease,background-color 220ms ease}
	.rx-about-capability:nth-child(4n+1){padding-left:0}
	.rx-about-capability i{color:#00a47c;font-size:.9rem}
	.rx-about-capability:hover,.rx-about-capability:focus-visible{background:#f5fbf8;color:#007f69}
	.rx-about-capability-link{margin-top:25px}
	.rx-about-text-link{color:#007f69}
	.rx-about-approach{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));border-top:1px solid #dce6ec;border-bottom:1px solid #dce6ec}
	.rx-about-step{padding:28px 30px 30px 0}
	.rx-about-step+.rx-about-step{padding-left:30px;border-left:1px solid #dce6ec}
	.rx-about-step-number{display:block;margin-bottom:21px;color:#00a47c;font-size:.78rem;font-weight:800;letter-spacing:.12em}
	.rx-about-step h3{margin:0 0 11px;color:#103354;font-size:1.28rem}
	.rx-about-step p{margin:0;color:#526477;line-height:1.72}
	.rx-about-team-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px}
	.rx-about-team-card{overflow:hidden;border:1px solid #e0e8ed;border-radius:14px;background:#fff}
	.rx-about-team-card.has-photo .rx-about-team-photo{display:block;width:100%;aspect-ratio:4/3;object-fit:cover;background:#f3f7fa}
	.rx-about-team-copy{padding:20px 21px 22px}
	.rx-about-team-copy h3{margin:0 0 5px;color:#103354;font-size:1.18rem}
	.rx-about-team-role{display:block;margin-bottom:10px;color:#008f71;font-size:.9rem;font-weight:700}
	.rx-about-team-copy p{margin:0;color:#526477;line-height:1.65}
	@media(max-width:991.98px){.rx-about-hero{padding:7.8rem 0 5.5rem}.rx-about-hero-grid,.rx-about-section-head{grid-template-columns:1fr;gap:34px}.rx-about-highlights,.rx-about-values{grid-template-columns:repeat(2,minmax(0,1fr))}.rx-about-highlight:nth-child(3),.rx-about-highlight:nth-child(4),.rx-about-value:nth-child(3),.rx-about-value:nth-child(4){border-top:1px solid #dce6ec}.rx-about-highlight:nth-child(3),.rx-about-value:nth-child(3){border-left:0;padding-left:0}.rx-about-capabilities{grid-template-columns:repeat(2,minmax(0,1fr))}.rx-about-capability:nth-child(2n+1){padding-left:0}.rx-about-team-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
	@media(max-width:767.98px){.rx-about-container{width:min(calc(100% - 32px),560px)}.rx-about-hero{padding:7rem 0 4.5rem}.rx-about-hero h1{font-size:clamp(2.35rem,10.5vw,3.2rem)}.rx-about-hero-visual{min-height:270px}.rx-about-section{padding:68px 0}.rx-about-who{padding-top:70px;padding-bottom:70px}.rx-about-who-grid{grid-template-columns:1fr;gap:24px}.rx-about-who-heading h2{max-width:520px;font-size:clamp(2rem,8vw,2.65rem)}.rx-about-who-heading .rx-about-eyebrow{margin-bottom:14px}.rx-about-who-copy{padding-top:0}.rx-about-who-lead{margin-bottom:21px;padding-left:16px;font-size:1.12rem}.rx-about-section-head{gap:18px;margin-bottom:30px}.rx-about-story-copy,.rx-about-mission-grid,.rx-about-approach{grid-template-columns:1fr;gap:0}.rx-about-story-copy{gap:17px}.rx-about-highlight{min-height:104px;padding:20px 14px}.rx-about-highlight:nth-child(odd),.rx-about-value:first-child,.rx-about-value:nth-child(3){padding-left:0}.rx-about-highlight:nth-child(even),.rx-about-value:nth-child(2){border-left:1px solid #dce6ec}.rx-about-mission{padding:26px 0}.rx-about-mission+.rx-about-mission{padding:26px 0;border-top:1px solid #dce6ec;border-left:0}.rx-about-value{padding:22px 14px}.rx-about-value:nth-child(3),.rx-about-value:nth-child(4){border-top:1px solid #dce6ec}.rx-about-capability{min-height:72px;padding:14px 10px;font-size:.92rem}.rx-about-approach{gap:0}.rx-about-step,.rx-about-step+.rx-about-step{padding:23px 0;border-left:0}.rx-about-step+.rx-about-step{border-top:1px solid #dce6ec}.rx-about-team-grid{grid-template-columns:1fr;gap:18px}.rx-about-team-card.has-photo{display:grid;grid-template-columns:120px 1fr}.rx-about-team-card.has-photo .rx-about-team-photo{height:100%;min-height:150px;aspect-ratio:auto}.rx-about-team-copy{padding:17px}}
	@media(prefers-reduced-motion:reduce){.rx-about-primary-link,.rx-about-arrow,.rx-about-capability{transition:none!important}}
</style>
@endpush

@section('content')
<div class="rx-about-page">
	<section class="rx-about-hero">
		<div class="rx-about-container rx-about-hero-grid">
			<div>
				<div class="rx-about-eyebrow">{{ $page?->hero_eyebrow ?: 'About Rexxsol' }}</div>
				<h1>{{ $page?->hero_heading ?: 'Technology expertise built through years of real-world experience.' }}</h1>
				<p class="rx-about-hero-copy">{{ $page?->hero_description ?: 'For more than a decade, Rexxsol has been helping businesses design, build and improve digital products, software systems and connected experiences.' }}</p>
				<p class="rx-about-experience"><i class="fa fa-check-circle" aria-hidden="true"></i>14+ years of experience in software and web development.</p>
				<a class="rx-about-primary-link" href="{{ $withPreview(route('website.services')) }}">Explore our services <i class="fa fa-arrow-right rx-about-arrow" aria-hidden="true"></i></a>
			</div>
			<div class="rx-about-hero-visual"><img src="{{ $aboutIllustration }}" alt="{{ $page?->featured_image ? \App\Support\ContentImage::alt($page->featured_image, $page->featured_image_alt, $page->title) : 'Rexxsol technology and digital solutions illustration' }}"></div>
		</div>
	</section>

	<section class="rx-about-section rx-about-who">
		<div class="rx-about-container rx-about-who-grid">
			<div class="rx-about-who-heading"><div class="rx-about-eyebrow">Who we are</div><h2>Practical technology. Long-term partnerships.</h2></div>
			<div class="rx-about-who-copy">
				<p class="rx-about-who-lead">We build technology around real business needs.</p>
				<div class="rx-about-story-copy">
					<p>We deliver web and mobile products, custom software, integrations, automation, and user-focused design.</p>
					<p>Ongoing support helps each solution stay useful as your business evolves.</p>
				</div>
			</div>
		</div>
	</section>

	<section class="rx-about-section rx-about-section--soft">
		<div class="rx-about-container rx-about-highlights">
			@foreach ([['14+ Years Experience', 'Software and web development'], ['Software & Web Development', 'Practical technology expertise'], ['End-to-End Digital Solutions', 'From discovery through delivery'], ['Long-Term Client Support', 'Ongoing care and improvement']] as $highlight)
				<div class="rx-about-highlight"><strong>{{ $highlight[0] }}</strong><span>{{ $highlight[1] }}</span></div>
			@endforeach
		</div>
	</section>

	<section class="rx-about-section">
		<div class="rx-about-container rx-about-mission-grid">
			<article class="rx-about-mission"><span class="rx-about-mission-mark"><i class="fa fa-compass" aria-hidden="true"></i></span><h2>Our Mission</h2><p>To create reliable digital solutions that simplify work, improve experiences and support sustainable business growth.</p></article>
			<article class="rx-about-mission"><span class="rx-about-mission-mark"><i class="fa fa-eye" aria-hidden="true"></i></span><h2>Our Vision</h2><p>To be a trusted technology partner for organizations looking to evolve through practical, well-designed digital solutions.</p></article>
		</div>
	</section>

	<section class="rx-about-section rx-about-section--soft">
		<div class="rx-about-container">
			<div class="rx-about-section-head"><div><div class="rx-about-eyebrow">Our values</div><h2>Principles that guide how we work.</h2></div><p>These principles shape how we make decisions, work with clients, and deliver technology over the long term.</p></div>
			<div class="rx-about-values">
				@foreach ([['fa-lightbulb', 'Practical Innovation', 'We use technology with a clear purpose and focus on solutions that create real value.'], ['fa-shield-alt', 'Reliability', 'We value clear communication, dependable delivery and maintainable solutions.'], ['fa-handshake', 'Collaboration', 'We work closely with clients to understand their goals, challenges and priorities.'], ['fa-seedling', 'Continuous Growth', 'We keep improving our skills, technology and processes as business needs evolve.']] as $value)
					<article class="rx-about-value"><span class="rx-about-value-icon"><i class="fa {{ $value[0] }}" aria-hidden="true"></i></span><h3>{{ $value[1] }}</h3><p>{{ $value[2] }}</p></article>
				@endforeach
			</div>
		</div>
	</section>

	<section class="rx-about-section">
		<div class="rx-about-container">
			<div class="rx-about-section-head"><div><div class="rx-about-eyebrow">What we do</div><h2>Digital capabilities built around business needs.</h2></div><p>A connected range of capabilities helps us support products and teams through each stage of their digital journey.</p></div>
			<div class="rx-about-capabilities">
				@foreach (['Custom Web Development', 'Mobile App Development', 'Custom Software Development', 'UI/UX Design', 'API & Software Integration', 'Quality Assurance & Testing', 'Support & Maintenance', 'Business Automation Solutions'] as $capability)
					<a class="rx-about-capability" href="{{ $withPreview(route('website.services')) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i><span>{{ $capability }}</span></a>
				@endforeach
			</div>
			<div class="rx-about-capability-link"><a class="rx-about-text-link" href="{{ $withPreview(route('website.services')) }}">Explore all services <i class="fa fa-arrow-right rx-about-arrow" aria-hidden="true"></i></a></div>
		</div>
	</section>

	<section class="rx-about-section rx-about-section--soft">
		<div class="rx-about-container">
			<div class="rx-about-section-head"><div><div class="rx-about-eyebrow">Our approach</div><h2>A technology partner focused on the bigger picture.</h2></div><p>We balance business context, thoughtful delivery, and long-term ownership at every stage of the work.</p></div>
			<div class="rx-about-approach">
				@foreach ([['Understand First', 'We take time to understand the business need, users and constraints before choosing the solution.'], ['Build With Purpose', 'We focus on practical architecture, usability and maintainability without unnecessary complexity.'], ['Support Beyond Launch', 'We help improve, maintain and evolve solutions after they go live.']] as $index => $step)
					<article class="rx-about-step"><span class="rx-about-step-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>
				@endforeach
			</div>
		</div>
	</section>

	@if ($teamMembers->isNotEmpty())
		<section class="rx-about-section">
			<div class="rx-about-container">
				<div class="rx-about-section-head"><div><div class="rx-about-eyebrow">Our team</div><h2>The people behind Rexxsol.</h2></div><p>Meet the people bringing care, expertise, and perspective to our client partnerships.</p></div>
				<div class="rx-about-team-grid">
					@foreach ($teamMembers as $member)
						<article class="rx-about-team-card {{ $member->profile_image ? 'has-photo' : '' }}">
							@if ($member->profile_image)<img class="rx-about-team-photo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($member->profile_image) }}" alt="{{ \App\Support\ContentImage::alt($member->profile_image, $member->profile_image_alt, $member->name) }}">@endif
							<div class="rx-about-team-copy"><h3>{{ $member->name }}</h3><span class="rx-about-team-role">{{ $member->designation }}</span>@if ($member->short_bio)<p>{{ $member->short_bio }}</p>@endif</div>
						</article>
					@endforeach
				</div>
			</div>
		</section>
	@endif

</div>
@endsection