@php($settings = $section['settings'] ?? [])
@php($allItems = collect($section['items'] ?? []))
@php($isInternalCopy = static fn (array $item): bool => preg_match('/\b(?:demo|qa|placeholder|testing|production[- ]ready)\b|content model|designed to show|not official|local preview/i', implode(' ', [$item['title'] ?? '', $item['answer'] ?? ''])) === 1)
@php($reviewItems = $allItems->filter($isInternalCopy)->values())
@php($items = $allItems->reject($isInternalCopy)->values())
@if ($items->isEmpty() && filled($previewTheme ?? null))
    @php($items = collect([
        ['title' => 'How long will my project take?', 'answer' => "Timelines depend on your scope and requirements. We'll agree on a clear plan and milestones before development begins."],
        ['title' => 'Can you improve our existing website or software?', 'answer' => 'We can review what you have today and recommend practical improvements around your goals and current tools.'],
        ['title' => 'Can you automate our business workflows?', 'answer' => 'We can identify repetitive work and shape automation around the way your team operates.'],
        ['title' => 'Will we be able to manage our own content?', 'answer' => 'Yes. We can build clear content tools that help your team keep the information on your site up to date.'],
        ['title' => 'What happens after launch?', 'answer' => 'We can agree on support and next steps that fit your team after the launch.'],
    ]))
@endif
@if ($items->isNotEmpty())
<section class="nova-section nova-faq" aria-labelledby="nova-faq-title" @if ($reviewItems->isNotEmpty()) data-editorial-review-count="{{ $reviewItems->count() }}" @endif>
    <div class="nova-container nova-faq-grid">
        <div class="nova-faq-intro">
            <p class="nova-kicker">FAQs</p>
            <h2 id="nova-faq-title">A little clarity,<br>before we begin.</h2>
            <p>Have something else in mind?<br>Let's talk about your project.</p>
            <a class="nova-faq-contact" href="{{ route('website.contact') }}">Get in Touch <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <span class="nova-faq-chat" aria-hidden="true"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></span>
        </div>
        <div class="nova-faq-list" data-nova-faq>
            @foreach ($items as $index => $item)
                @php($panelId = 'nova-faq-answer-'.$index)
                <article class="nova-faq-item is-expanded">
                    <h3><button type="button" id="nova-faq-question-{{ $index }}" aria-expanded="true" aria-controls="{{ $panelId }}"><span>{{ $item['title'] ?? 'Question' }}</span><span class="nova-faq-toggle" aria-hidden="true"><i class="fa-solid fa-plus"></i><i class="fa-solid fa-minus"></i></span></button></h3>
                    <div id="{{ $panelId }}" class="nova-faq-answer" role="region" aria-labelledby="nova-faq-question-{{ $index }}"><div class="nova-faq-answer-inner"><p>{{ $item['answer'] ?? '' }}</p></div></div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif