<?php

use App\Models\BlogPost;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\Faq;
use App\Models\Media;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin')] #[Title('Rexxsol CMS Dashboard')] class extends Component {
    #[Computed]
    public function stats(): array
    {
        return [
            ['label' => 'Total Pages', 'value' => Page::count(), 'icon' => 'document-text', 'detail' => Page::where('status', 'published')->count().' published'],
            ['label' => 'Active Services', 'value' => Service::where('is_active', true)->count(), 'icon' => 'briefcase', 'detail' => 'Available on the website'],
            ['label' => 'Published Projects', 'value' => Project::where('status', 'published')->count(), 'icon' => 'rectangle-stack', 'detail' => 'Visible in the portfolio'],
            ['label' => 'Blog Posts', 'value' => BlogPost::count(), 'icon' => 'newspaper', 'detail' => BlogPost::where('status', 'published')->count().' published'],
            ['label' => 'Team Members', 'value' => TeamMember::count(), 'icon' => 'users', 'detail' => TeamMember::where('status', 'published')->count().' published'],
            ['label' => 'Testimonials', 'value' => Testimonial::count(), 'icon' => 'chat-bubble-left-right', 'detail' => Testimonial::where('status', 'published')->count().' published'],
            ['label' => 'FAQs', 'value' => Faq::count(), 'icon' => 'question-mark-circle', 'detail' => Faq::where('status', 'published')->count().' published'],
            ['label' => 'Media Assets', 'value' => Media::count(), 'icon' => 'photo', 'detail' => 'Available in the library'],
        ];
    }

    #[Computed]
    public function publishedContent(): int
    {
        return Page::where('status', 'published')->count()
            + Project::where('status', 'published')->count()
            + BlogPost::where('status', 'published')->count();
    }

    #[Computed]
    public function draftContent(): int
    {
        return Page::where('status', 'draft')->count()
            + Project::where('status', 'draft')->count()
            + BlogPost::where('status', 'draft')->count();
    }

    #[Computed]
    public function lastContentUpdate(): ?Carbon
    {
        return collect([
            Page::max('updated_at'),
            Service::max('updated_at'),
            Project::max('updated_at'),
            BlogPost::max('updated_at'),
        ])->filter()->map(fn (string $date): Carbon => Carbon::parse($date))->sortDesc()->first();
    }

    #[Computed]
    public function recentContacts()
    {
        return ContactMessage::query()->latest()->limit(5)->get();
    }

    #[Computed]
    public function recentActivity()
    {
        return ActivityLog::with('user')->latest('id')->limit(6)->get();
    }

    #[Computed]
    public function recentPages()
    {
        return Page::query()->latest('updated_at')->limit(6)->get(['id', 'title', 'updated_at']);
    }

    public function todayLabel(): string
    {
        return now()->format('D, d M Y');
    }
}; ?>

<div class="mx-auto max-w-[1520px] space-y-4 pb-4">
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#000080]">Overview</p>
            <h2 class="mt-1 text-[26px] font-bold tracking-tight text-[#0f2347]">Good afternoon, {{ auth()->user()->name }} <span aria-hidden="true">👋</span></h2>
            <p class="mt-1 text-sm text-[#64748b]">Manage your Rexxsol website and digital content from one place.</p>
        </div>
        <div class="inline-flex items-center gap-2 self-start rounded-lg border border-[#e5eaf2] bg-white px-3 py-2 text-xs font-semibold text-[#334155] shadow-[0_1px_2px_rgba(15,23,42,0.025)] sm:self-auto">
            <flux:icon name="calendar-days" class="size-4 text-[#000080]" />
            {{ $this->todayLabel() }}
        </div>
    </section>

    <section aria-labelledby="content-snapshot-heading">
        <div class="mb-2 flex items-center justify-between">
            <h3 id="content-snapshot-heading" class="text-sm font-bold text-[#172033]">Content snapshot</h3>
            <span class="text-xs text-[#94a3b8]">Live database totals</span>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach (array_slice($this->stats, 0, 4) as $index => $stat)
                @php($tone = [['bg' => '#f3f8ff', 'icon' => '#e0efff', 'text' => '#0b57d0'], ['bg' => '#f1fbf5', 'icon' => '#dcfce7', 'text' => '#12a150'], ['bg' => '#f7f4ff', 'icon' => '#ede9fe', 'text' => '#7c3aed'], ['bg' => '#fff7ee', 'icon' => '#ffedd5', 'text' => '#f59e0b']][$index])
                <article class="rounded-[10px] border border-[#e5eaf2] p-4 shadow-[0_1px_2px_rgba(15,23,42,0.025)]" style="background: {{ $tone['bg'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium text-[#64748b]">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-[28px] font-bold leading-none tracking-tight text-[#172033]">{{ number_format($stat['value']) }}</p>
                            <p class="mt-2 text-[11px] text-[#64748b]">{{ $stat['detail'] }}</p>
                        </div>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg" style="background: {{ $tone['icon'] }}; color: {{ $tone['text'] }}"><flux:icon :name="$stat['icon']" class="size-5" /></span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-3 xl:grid-cols-[minmax(0,1.6fr)_minmax(260px,0.8fr)]">
        <article class="rounded-[10px] border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-[#172033]">Website Analytics</h3>
                    <p class="mt-1 text-xs text-[#64748b]">Track your website performance and visitor insights.</p>
                </div>
                <button type="button" disabled class="inline-flex items-center gap-2 rounded-md border border-[#e5eaf2] bg-white px-2.5 py-1.5 text-xs font-medium text-[#64748b] opacity-80"><span>Last 30 Days</span><flux:icon name="chevron-down" class="size-3.5" /></button>
            </div>
            <div class="mt-4 flex min-h-[178px] flex-col items-center justify-center rounded-lg border border-dashed border-[#dbe5f1] bg-[#fbfdff] px-5 text-center">
                <span class="flex size-9 items-center justify-center rounded-lg bg-[#eef4ff] text-[#0b57d0]"><flux:icon name="chart-bar" class="size-5" /></span>
                <p class="mt-3 text-sm font-semibold text-[#334155]">Analytics data is not connected</p>
                <p class="mt-1 max-w-sm text-xs leading-5 text-[#64748b]">Visitor and pageview trends will appear here once website tracking is connected.</p>
            </div>
        </article>

        <article class="rounded-[10px] border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#172033]">Top Countries</h3>
                    <p class="mt-1 text-xs text-[#64748b]">Visitor geography</p>
                </div>
                <span class="text-xs font-semibold text-[#0b57d0]">View All <span aria-hidden="true">›</span></span>
            </div>
            <div class="flex min-h-[178px] items-center justify-center text-center">
                <p class="max-w-[220px] text-xs leading-5 text-[#64748b]">Country insights will appear when analytics tracking is connected.</p>
            </div>
        </article>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Total Visitors', 'eye'], ['Pageviews', 'document-text'], ['Avg. Session Duration', 'clock'], ['Bounce Rate', 'chart-bar']] as [$label, $icon])
            <article class="rounded-[10px] border border-[#e5eaf2] bg-white p-3.5 shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-[#eef4ff] text-[#0b57d0]"><flux:icon :name="$icon" class="size-4.5" /></span>
                    <div><p class="text-[11px] text-[#64748b]">{{ $label }}</p><p class="mt-0.5 text-lg font-bold leading-none text-[#172033]">--</p></div>
                </div>
                <p class="mt-2 text-[10px] text-[#94a3b8]">Analytics not connected</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-3 xl:grid-cols-2">
        <article class="overflow-hidden rounded-[10px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
            <div class="flex items-center justify-between border-b border-[#eef1f6] px-4 py-3"><div><h3 class="text-sm font-bold text-[#172033]">Most Visited Pages</h3><p class="mt-0.5 text-xs text-[#64748b]">Recently updated site pages</p></div><span class="text-xs font-semibold text-[#0b57d0]">View All <span aria-hidden="true">›</span></span></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[430px] text-left text-xs"><thead class="bg-[#fbfcfe] text-[10px] uppercase tracking-[0.12em] text-[#94a3b8]"><tr><th class="w-12 px-4 py-2.5">#</th><th class="px-2 py-2.5">Page Title</th><th class="px-4 py-2.5 text-right">Views</th></tr></thead><tbody class="divide-y divide-[#eef1f6]">@forelse ($this->recentPages as $index => $page)<tr><td class="px-4 py-2.5 text-[#94a3b8]">{{ $index + 1 }}</td><td class="px-2 py-2.5 font-medium text-[#0b57d0]">{{ $page->title }}</td><td class="px-4 py-2.5 text-right text-[#64748b]">Not connected</td></tr>@empty<tr><td colspan="3" class="px-4 py-8 text-center text-[#64748b]">No pages have been created yet.</td></tr>@endforelse</tbody></table></div>
        </article>

        <article class="overflow-hidden rounded-[10px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
            <div class="flex items-center justify-between border-b border-[#eef1f6] px-4 py-3"><div><h3 class="text-sm font-bold text-[#172033]">Top Browsers</h3><p class="mt-0.5 text-xs text-[#64748b]">Visitor technology</p></div><span class="text-xs font-semibold text-[#0b57d0]">View All <span aria-hidden="true">›</span></span></div>
            <div class="flex min-h-[206px] items-center justify-center px-4 text-center"><p class="max-w-xs text-xs leading-5 text-[#64748b]">Browser insights will appear when analytics tracking is connected.</p></div>
        </article>
    </section>

    <section class="grid gap-3 xl:grid-cols-2">
        <article class="overflow-hidden rounded-[10px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
            <div class="flex items-center justify-between border-b border-[#eef1f6] px-4 py-3"><div><h3 class="text-sm font-bold text-[#172033]">Recent Contact Messages</h3><p class="mt-0.5 text-xs text-[#64748b]">Latest enquiries from your website</p></div>@can('contact_messages.view')<a href="{{ route('admin.contact-messages') }}" wire:navigate class="text-xs font-semibold text-[#0b57d0]">View All <span aria-hidden="true">›</span></a>@endcan</div>
            <div class="overflow-x-auto"><table class="w-full min-w-[520px] text-left text-xs"><thead class="bg-[#fbfcfe] text-[10px] uppercase tracking-[0.12em] text-[#94a3b8]"><tr><th class="w-12 px-4 py-2.5">#</th><th class="px-2 py-2.5">Name</th><th class="px-2 py-2.5">Subject</th><th class="px-4 py-2.5 text-right">Date</th></tr></thead><tbody class="divide-y divide-[#eef1f6]">@forelse ($this->recentContacts as $index => $message)<tr><td class="px-4 py-2.5 text-[#94a3b8]">{{ $index + 1 }}</td><td class="px-2 py-2.5"><span class="font-semibold text-[#172033]">{{ $message->name }}</span><span class="block text-[10px] text-[#64748b]">{{ $message->email }}</span></td><td class="max-w-[180px] truncate px-2 py-2.5 text-[#475569]">{{ $message->subject ?: 'General enquiry' }}</td><td class="whitespace-nowrap px-4 py-2.5 text-right text-[#64748b]">{{ $message->created_at?->format('d M Y') }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-8 text-center text-[#64748b]">No contact messages yet.</td></tr>@endforelse</tbody></table></div>
        </article>

        <article class="rounded-[10px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.025)]">
            <div class="flex items-center justify-between border-b border-[#eef1f6] px-4 py-3"><div><h3 class="text-sm font-bold text-[#172033]">Recent Activity</h3><p class="mt-0.5 text-xs text-[#64748b]">Recorded CMS changes</p></div>@can('view-activity-logs')<a href="{{ route('admin.activity-logs') }}" wire:navigate class="text-xs font-semibold text-[#0b57d0]">View All <span aria-hidden="true">›</span></a>@endcan</div>
            @can('view-activity-logs')<div class="divide-y divide-[#eef1f6] px-4">@forelse ($this->recentActivity as $activity)<div class="flex gap-3 py-3"><span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#eef4ff] text-[#0b57d0]"><flux:icon name="clock" class="size-4" /></span><div class="min-w-0 flex-1"><p class="text-xs font-semibold text-[#172033]">{{ $activity->user?->name ?? 'System' }}</p><p class="truncate text-xs text-[#64748b]">{{ $activity->action }} {{ $activity->module }}{{ $activity->subject_label ? ' · '.$activity->subject_label : '' }}</p></div><time class="shrink-0 text-[10px] text-[#94a3b8]">{{ $activity->created_at?->diffForHumans() }}</time></div>@empty<div class="py-10 text-center text-xs text-[#64748b]">No activity has been recorded yet.</div>@endforelse</div>@else<div class="px-4 py-10 text-center text-xs text-[#64748b]">Activity history is available to Super Admins.</div>@endcan
        </article>
    </section>
</div>
