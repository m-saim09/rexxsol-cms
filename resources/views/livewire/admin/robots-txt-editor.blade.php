<div class="mx-auto w-full max-w-[1440px] space-y-5 pb-5"
     x-data="{ dirty: @js(! hash_equals($baselineHash, hash('sha256', $content))) }"
     x-effect="dirty = @js(! hash_equals($baselineHash, hash('sha256', $content)))"
     x-on:livewire:navigate.window="if (dirty && ! window.confirm('You have unsaved robots.txt changes. Leave this page and discard them?')) $event.preventDefault()"
     x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }">
    @php
        $dirty = ! hash_equals($baselineHash, hash('sha256', $content));
        $lineCount = max(1, substr_count($content, "\n") + 1);
    @endphp

    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs text-[#64748b]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:text-[#2563eb] hover:underline">Dashboard</a>
        <span aria-hidden="true">/</span>
        <span>Appearance</span>
        <span aria-hidden="true">/</span>
        <span class="font-medium text-[#172033]" aria-current="page">Robots.txt Editor</span>
    </nav>

    <header>
        <h1 class="text-2xl font-bold tracking-tight text-[#172033]">Robots.txt Editor</h1>
        <p class="mt-1 text-sm text-[#64748b]">Control how search engine crawlers access your website.</p>
    </header>

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(250px,1fr)]">
        <section aria-labelledby="robots-editor-heading" class="overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
            <div class="border-b border-[#eef1f6] p-4 sm:p-5">
                <div class="flex items-start gap-3 rounded-[7px] border border-blue-100 bg-blue-50/70 p-3.5">
                    <span aria-hidden="true" class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-md bg-white text-[#2563eb]"><flux:icon name="information-circle" class="size-4" /></span>
                    <p class="text-xs leading-5 text-[#475569]">Robots.txt controls crawling. It does not protect private content or guarantee removal from search results.</p>
                </div>
            </div>

            <div class="space-y-4 p-4 sm:p-5">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 id="robots-editor-heading" class="text-sm font-semibold text-[#172033]">Robots.txt content</h2>
                        <p class="mt-1 text-xs text-[#64748b]">Use one directive per line. Comments can start with #.</p>
                    </div>
                    @if ($sitemapAvailable && auth()->user()->can('seo.update'))
                        <button type="button" wire:click="insertSitemap" class="inline-flex h-8 items-center gap-1.5 rounded-[5px] px-2.5 text-xs font-semibold text-[#2563eb] transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/30">
                            <flux:icon name="link" class="size-3.5" />Insert sitemap URL
                        </button>
                    @endif
                </div>

                <div x-data="{ lineCount: {{ $lineCount }} }" x-on:robots-content-changed.window="$nextTick(() => lineCount = $refs.robotsEditor.value.split('\n').length)" class="overflow-hidden rounded-[7px] border border-[#cbd5e1] bg-white transition focus-within:border-[#2563eb] focus-within:ring-2 focus-within:ring-[#2563eb]/15">
                    <div class="flex h-[420px] min-h-[350px] max-h-[65vh]">
                        <div x-ref="lineGutter" aria-hidden="true" class="w-12 shrink-0 overflow-hidden border-r border-[#e5eaf2] bg-[#f8fafc] py-3 text-right font-mono text-xs leading-6 text-[#94a3b8] select-none">
                            <template x-for="line in lineCount" :key="line"><div class="h-6 pr-3" x-text="line"></div></template>
                        </div>
                        <textarea id="robots-content"
                                  x-ref="robotsEditor"
                                  wire:model.live.debounce.300ms="content"
                                  x-on:input="lineCount = $el.value.split('\n').length"
                                  x-on:scroll="$refs.lineGutter.scrollTop = $el.scrollTop"
                                  @if (! auth()->user()->can('seo.update')) readonly @endif
                                  spellcheck="false"
                                  autocapitalize="off"
                                  autocomplete="off"
                                  autocorrect="off"
                                  wrap="off"
                                  aria-label="Robots.txt content editor"
                                  class="min-w-0 flex-1 resize-none overflow-auto bg-white px-4 py-3 font-mono text-[13px] leading-6 text-[#172033] outline-none placeholder:text-[#94a3b8] disabled:cursor-not-allowed"
                                  placeholder="User-agent: *&#10;Disallow:">{{ $content }}</textarea>
                    </div>
                </div>
                @error('content')<p class="text-xs font-medium text-red-700" role="alert">{{ $message }}</p>@enderror
                @error('save')<p class="rounded-[6px] border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700" role="alert">{{ $message }}</p>@enderror

                @if ($analysis['errors'])
                    <section aria-label="Robots.txt validation errors" class="rounded-[7px] border border-red-200 bg-red-50 p-3">
                        <h3 class="text-xs font-semibold text-red-800">Fix these lines before saving</h3>
                        <ul class="mt-2 space-y-1 text-xs text-red-700">
                            @foreach ($analysis['errors'] as $issue)<li><span class="font-semibold">Line {{ $issue['line'] }}:</span> {{ $issue['message'] }}</li>@endforeach
                        </ul>
                    </section>
                @endif
                @if ($analysis['warnings'])
                    <section aria-label="Robots.txt compatibility warnings" class="rounded-[7px] border border-amber-200 bg-amber-50 p-3">
                        <h3 class="text-xs font-semibold text-amber-900">Review compatibility warnings</h3>
                        <ul class="mt-2 space-y-1 text-xs text-amber-800">
                            @foreach ($analysis['warnings'] as $issue)<li><span class="font-semibold">Line {{ $issue['line'] }}:</span> {{ $issue['message'] }}</li>@endforeach
                        </ul>
                    </section>
                @endif
                @if ($analysis['blocks_all'])
                    <label class="flex items-start gap-2.5 rounded-[7px] border border-red-200 bg-red-50 p-3 text-xs leading-5 text-red-800">
                        <input type="checkbox" wire:model="blockingConfirmed" class="mt-0.5 size-4 rounded border-red-300 text-red-600 focus:ring-red-500" />
                        <span><span class="font-semibold">This blocks all crawlers from the site.</span> I understand and intentionally want to publish this rule.</span>
                    </label>
                    @error('blockingConfirmed')<p class="text-xs font-medium text-red-700" role="alert">{{ $message }}</p>@enderror
                @endif

                <p class="rounded-[6px] bg-[#f8fafc] px-3 py-2.5 text-xs leading-5 text-[#64748b]">
                    A robots.txt <code class="rounded bg-white px-1 py-0.5 font-mono text-[#475569]">noindex</code> directive is not reliable for preventing indexing. Use page meta robots or <code class="rounded bg-white px-1 py-0.5 font-mono text-[#475569]">X-Robots-Tag</code> where appropriate.
                </p>

                <div class="flex flex-col gap-2 border-t border-[#eef1f6] pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-[#64748b]">Public file: <a href="{{ route('robots') }}" target="_blank" rel="noopener" class="font-medium text-[#2563eb] hover:underline">{{ url('/robots.txt') }}</a></p>
                    <a href="{{ route('robots') }}" target="_blank" rel="noopener" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-[5px] px-2 text-xs font-semibold text-[#2563eb] hover:bg-blue-50"><flux:icon name="arrow-top-right-on-square" class="size-3.5" />View live robots.txt</a>
                </div>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="rounded-[8px] border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
                <h2 class="text-sm font-semibold text-[#172033]">Publish changes</h2>
                <p class="mt-1 text-xs leading-5 text-[#64748b]">Changes are published to the public robots.txt file only after saving.</p>

                <div class="mt-4 flex items-center gap-2 rounded-[6px] {{ $dirty ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800' }} px-3 py-2 text-xs font-semibold" role="status">
                    <span class="size-2 rounded-full {{ $dirty ? 'bg-amber-500' : 'bg-emerald-500' }}" aria-hidden="true"></span>{{ $dirty ? 'Unsaved changes' : 'All changes saved' }}
                </div>
                @if (auth()->user()->can('seo.update'))
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="mt-4 inline-flex h-10 w-full items-center justify-center gap-2 rounded-[6px] bg-[#2563eb] px-4 text-sm font-semibold text-white transition hover:bg-[#1d4ed8] disabled:cursor-wait disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/40 focus-visible:ring-offset-2">
                        <span wire:loading.remove wire:target="save">Save changes</span><span wire:loading wire:target="save">Saving…</span>
                    </button>
                    @error('save')<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
                    <button type="button" wire:click="restoreDefaults" wire:confirm="Replace the editor contents with the default crawl-permitting rules? The live file will not change until you save." class="mt-2 inline-flex h-9 w-full items-center justify-center rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-xs font-semibold text-[#475569] transition hover:bg-[#f7f9fc] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/30">Restore default</button>
                    @if ($dirty)
                        <button type="button" wire:click="reloadLatest" wire:confirm="Discard your unsaved edits and load the latest published robots.txt?" class="mt-2 w-full rounded-[6px] px-3 py-2 text-xs font-semibold text-[#64748b] transition hover:bg-[#f7f9fc] hover:text-[#172033]">Reload latest published version</button>
                    @endif
                @else
                    <p class="mt-4 rounded-[6px] bg-[#f8fafc] px-3 py-2 text-xs text-[#64748b]">You have view-only access. Contact an SEO administrator to publish changes.</p>
                @endif

                <div class="mt-5 border-t border-[#eef1f6] pt-4">
                    <h3 class="text-xs font-semibold text-[#475569]">Last saved</h3>
                    @if ($savedAt)
                        <p class="mt-1 text-xs text-[#172033]">{{ \Illuminate\Support\Carbon::parse($savedAt)->timezone(config('app.timezone'))->format('M j, Y, g:i A') }}</p>
                        <p class="mt-0.5 text-xs text-[#64748b]">by {{ $savedBy ?: 'Unknown user' }} · {{ config('app.timezone') }}</p>
                    @else
                        <p class="mt-1 text-xs text-[#64748b]">No saved custom version yet.</p>
                    @endif
                </div>
                <a href="{{ route('robots') }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-[#2563eb] hover:underline"><flux:icon name="arrow-top-right-on-square" class="size-3.5" />View live robots.txt</a>
            </section>

            <section class="rounded-[8px] border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
                <h2 class="text-sm font-semibold text-[#172033]">Upload a file</h2>
                <p class="mt-1 text-xs leading-5 text-[#64748b]">Choose a UTF-8 plain-text .txt file up to 100 KB. It loads into the editor for review; upload does not publish.</p>
                @if (auth()->user()->can('seo.update'))
                    <label for="robots-upload" class="mt-3 block text-xs font-semibold text-[#475569]">Robots.txt file</label>
                    <input id="robots-upload" type="file" wire:model="upload" accept=".txt,text/plain" class="mt-1.5 block w-full text-xs text-[#475569] file:mr-3 file:h-9 file:rounded-[6px] file:border file:border-[#dbe2ed] file:bg-white file:px-3 file:text-xs file:font-semibold file:text-[#172033] hover:file:bg-[#f7f9fc]" />
                    @error('upload')<p class="mt-2 text-xs text-red-700" role="alert">{{ $message }}</p>@enderror
                    <div wire:loading wire:target="upload" class="mt-2 text-xs text-[#64748b]" role="status">Uploading file for validation…</div>
                    <button type="button" wire:click="loadUpload" wire:loading.attr="disabled" wire:target="loadUpload,upload" class="mt-3 inline-flex h-9 w-full items-center justify-center rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-xs font-semibold text-[#172033] transition hover:bg-[#f7f9fc] disabled:opacity-50">Load into editor</button>
                @endif
            </section>
        </aside>
    </div>
</div>
