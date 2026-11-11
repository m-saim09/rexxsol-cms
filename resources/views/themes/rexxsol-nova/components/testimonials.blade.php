@php($items = $section['items'] ?? [])
@php($isPreview = (bool) ($previewTheme ?? false))
@if (empty($items) && $isPreview)
    @php($items = [
        ['title' => 'Client Name', 'company' => 'Company / Role', 'quote' => 'A smooth experience, from the first conversation to the final delivery.'],
        ['title' => 'Client Name', 'company' => 'Company / Role', 'quote' => 'Clear communication and thoughtful solutions for our business needs.'],
        ['title' => 'Client Name', 'company' => 'Company / Role', 'quote' => 'A collaborative team that made a complex project feel straightforward.'],
    ])
    @php($sampleReviews = true)
@else
    @php($sampleReviews = false)
@endif
@if (! empty($items))
    <section class="nova-testimonials" role="region" aria-roledescription="carousel" aria-label="Client testimonials" aria-labelledby="nova-testimonials-title" data-nova-testimonials tabindex="0">
        <div class="nova-container">
            <div class="nova-testimonials-heading">
                <div class="nova-section-heading">
                    <p class="nova-kicker">{{ $section['settings']['eyebrow'] ?? 'Testimonials' }}</p>
                    <h2 id="nova-testimonials-title">{{ $section['settings']['heading'] ?? 'What Our Clients Say' }}</h2>
                    <p>{{ $section['settings']['description'] ?? 'Thoughtful partnerships, built on trust and clear communication.' }}</p>
                </div>
                <div class="nova-slider-controls" data-testimonial-controls>
                    <button type="button" data-testimonial-prev aria-label="Previous testimonials"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                    <button type="button" data-testimonial-next aria-label="Next testimonials"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="nova-testimonial-viewport" data-testimonial-viewport>
                <div class="nova-testimonial-track" data-testimonial-track>
                    @foreach ($items as $item)
                        @php($name = $item['title'] ?? 'Client')
                        <article class="nova-testimonial-card">
                            @if ($sampleReviews)<span class="nova-sample-label">Sample review</span>@endif
                            <span class="nova-quote-mark" aria-hidden="true"><i class="fa-solid fa-quote-left"></i></span>
                            <blockquote>{{ $item['quote'] ?? '' }}</blockquote>
                            <div class="nova-review-author">
                                @if (! empty($item['image']))<img src="{{ $item['image'] }}" alt="{{ $item['image_alt'] ?? $name }}" loading="lazy">
                                @else<span class="nova-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1)) }}</span>@endif
                                <span><strong>{{ $name }}</strong><small>{{ collect([$item['company'] ?? null, $item['designation'] ?? null])->filter()->join(' / ') }}</small></span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="nova-slider-dots" data-testimonial-dots aria-label="Testimonial pages"></div>
        </div>
    </section>
@endif