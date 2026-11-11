<div class="mx-auto max-w-[1520px] space-y-4 pb-4">
    <x-admin.page-header title="Blog Reports" description="Review real publishing and taxonomy data from Rexxsol posts." />

    <section aria-labelledby="blog-report-snapshot">
        <div class="mb-2 flex items-center justify-between">
            <h2 id="blog-report-snapshot" class="text-sm font-bold text-[#172033]">Publishing snapshot</h2>
            <span class="text-xs text-[#94a3b8]">Live database totals</span>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <article class="rounded-[10px] border border-[#e5eaf2] p-4 shadow-[0_1px_2px_rgba(15,23,42,0.025)]" style="background: #e9f2ff">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-[#64748b]">Published posts</p>
                        <p class="mt-2 text-[28px] font-bold leading-none tracking-tight text-[#172033]">{{ number_format($published) }}</p>
                        <p class="mt-2 text-[11px] text-[#64748b]">Visible on the public blog</p>
                    </div>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#e0efff] text-[#0b57d0]"><flux:icon name="check-circle" class="size-5" /></span>
                </div>
            </article>
            <article class="rounded-[10px] border border-[#e5eaf2] p-4 shadow-[0_1px_2px_rgba(15,23,42,0.025)]" style="background: #ffefdc">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-[#64748b]">Draft posts</p>
                        <p class="mt-2 text-[28px] font-bold leading-none tracking-tight text-[#172033]">{{ number_format($drafts) }}</p>
                        <p class="mt-2 text-[11px] text-[#64748b]">Still in the editorial queue</p>
                    </div>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#ffedd5] text-[#f59e0b]"><flux:icon name="pencil-square" class="size-5" /></span>
                </div>
            </article>
        </div>
    </section>

    <section class="grid gap-3 xl:grid-cols-2">
        <article class="overflow-hidden rounded-[10px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
            <div class="flex items-center justify-between border-b border-[#eef1f6] px-4 py-3">
                <div>
                    <h2 class="text-sm font-bold text-[#172033]">Posts by category</h2>
                    <p class="mt-0.5 text-xs text-[#64748b]">Where your published content is grouped</p>
                </div>
                <span class="flex size-8 items-center justify-center rounded-lg bg-[#eef4ff] text-[#0b57d0]"><flux:icon name="tag" class="size-4" /></span>
            </div>
            <div class="divide-y divide-[#eef1f6]">
                @forelse ($categories as $category)
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-md bg-[#f5f7fb] text-xs font-semibold text-[#64748b]">{{ $loop->iteration }}</span>
                            <span class="truncate text-sm font-semibold text-[#172033]">{{ $category->name }}</span>
                        </div>
                        <span class="cms-badge shrink-0">{{ number_format($category->posts_count) }} {{ str('post')->plural($category->posts_count) }}</span>
                    </div>
                @empty
                    <p class="px-4 py-10 text-center text-sm text-[#64748b]">Category data will appear after posts are categorized.</p>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-[10px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
            <div class="flex items-center justify-between border-b border-[#eef1f6] px-4 py-3">
                <div>
                    <h2 class="text-sm font-bold text-[#172033]">Posts by author</h2>
                    <p class="mt-0.5 text-xs text-[#64748b]">Editorial contribution across the blog</p>
                </div>
                <span class="flex size-8 items-center justify-center rounded-lg bg-[#f1fbf5] text-[#12a150]"><flux:icon name="users" class="size-4" /></span>
            </div>
            <div class="divide-y divide-[#eef1f6]">
                @forelse ($authors as $author)
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-[#eaf7ef] text-xs font-bold text-[#12a150]">{{ str($author->author?->name ?? 'Unknown author')->substr(0, 1)->upper() }}</span>
                            <span class="truncate text-sm font-semibold text-[#172033]">{{ $author->author?->name ?? 'Unknown author' }}</span>
                        </div>
                        <span class="cms-badge shrink-0">{{ number_format($author->posts_count) }} {{ str('post')->plural($author->posts_count) }}</span>
                    </div>
                @empty
                    <p class="px-4 py-10 text-center text-sm text-[#64748b]">Author data will appear after posts are assigned.</p>
                @endforelse
            </div>
        </article>
    </section>
</div>
