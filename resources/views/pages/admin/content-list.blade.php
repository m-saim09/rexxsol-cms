<?php

use App\Support\CmsContent;
use App\Support\CmsPermissions;
use App\Support\HomepageContentService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] #[Title('Content management')] class extends Component {
    use WithPagination;

    public string $resource = 'pages';
    #[Url(as: 'q')] public string $search = '';
    #[Url] public string $status = '';
    public string $sortField = 'updated_at';
    public string $sortDirection = 'desc';
    public array $selected = [];
    public ?int $deletingId = null;
    public string $bulkAction = '';

    public function mount(string $resource): void
    {
        abort_unless(in_array($resource, ['pages', 'services', 'projects', 'blog'], true), 404);
        $this->resource = $resource;
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($resource, 'view')), 403);
    }

    public function updatedSearch(): void { $this->resetPage(); $this->selected = []; }
    public function updatedStatus(): void { $this->resetPage(); $this->selected = []; }

    public function sortBy(string $field): void
    {
        $sortableFields = array_unique(['id', 'created_at', 'updated_at', 'status', CmsContent::titleField($this->resource)]);

        if (! in_array($field, $sortableFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function toggleSelectAll(): void
    {
        $visibleIds = $this->items->pluck('id')->all();

        if (count(array_intersect($visibleIds, $this->selected)) === count($visibleIds)) {
            $this->selected = array_values(array_diff($this->selected, $visibleIds));
            return;
        }

        $this->selected = array_values(array_unique(array_merge($this->selected, $visibleIds)));
    }

    public function toggleSelected(int $id): void
    {
        $this->selected = in_array($id, $this->selected, true)
            ? array_values(array_diff($this->selected, [$id]))
            : [...$this->selected, $id];
    }

    public function applyBulkAction(): void
    {
        if ($this->bulkAction === '' || empty($this->selected)) {
            return;
        }

        $model = CmsContent::model($this->resource);

        match ($this->bulkAction) {
            'publish' => $this->bulkPublish($model),
            'draft' => $this->bulkDraft($model),
            'delete' => $this->bulkDelete($model),
            default => abort(422, 'Unsupported bulk action.'),
        };

        if ($this->resource === 'services') HomepageContentService::forget();

        $this->selected = [];
        $this->bulkAction = '';
        $this->resetPage();
        Flux::toast(text: 'The selected records were updated successfully.', heading: 'Bulk action completed', duration: 4000, variant: 'success');
    }

    public function confirmDelete(int $id): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'delete')), 403);
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'delete')), 403);
        $model = CmsContent::model($this->resource)::query()->findOrFail($this->deletingId);
        abort_if($this->resource === 'pages' && $model->is_system, 403, 'Core pages cannot be deleted.');
        $model->delete();
        if ($this->resource === 'services') HomepageContentService::forget();
        $this->deletingId = null;
        Flux::toast(text: 'The record has been removed successfully.', heading: CmsContent::singular($this->resource).' deleted', duration: 4000, variant: 'success');
    }

    protected function bulkPublish(string $model): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'publish')), 403);
        if ($this->resource === 'pages') {
            $model::query()->whereIn('id', $this->selected)->where('is_system', false)->get()->each(function ($page): void {
                $page->status = 'published';
                $page->published_at = now();
                $page->save();
            });
        } else {
            $model::query()->whereIn('id', $this->selected)->update(['status' => 'published', 'published_at' => now()]);
        }
        if ($this->resource === 'services') {
            $model::query()->whereIn('id', $this->selected)->update(['is_active' => true]);
        }
    }

    protected function bulkDelete(string $model): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'delete')), 403);
        $query = $model::query()->whereIn('id', $this->selected);
        if ($this->resource === 'pages') {
            $query->where('is_system', false)->get()->each->delete();
        } else {
            $query->delete();
        }
    }

    protected function bulkDraft(string $model): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'update')), 403);
        if ($this->resource === 'pages') {
            $model::query()->whereIn('id', $this->selected)->where('is_system', false)->get()->each(function ($page): void {
                $page->status = 'draft';
                $page->published_at = null;
                $page->save();
            });
        } else {
            $model::query()->whereIn('id', $this->selected)->update(['status' => 'draft', 'published_at' => null]);
        }
        if ($this->resource === 'services') {
            $model::query()->whereIn('id', $this->selected)->update(['is_active' => false]);
        }
    }

    public function togglePublish(int $id): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'publish')), 403);
        $model = CmsContent::model($this->resource)::query()->findOrFail($id);
        abort_if($this->resource === 'pages' && $model->is_system, 403, 'Core pages remain published at their existing routes.');
        $model->status = $model->status === 'published' ? 'draft' : 'published';
        $model->published_at = $model->status === 'published' ? now() : null;
        if ($this->resource === 'services') $model->is_active = $model->status === 'published';
        $model->save();
        Flux::toast(
            text: $model->status === 'published' ? 'The item is now visible on the website.' : 'This item is no longer publicly visible.',
            heading: $model->status === 'published' ? CmsContent::singular($this->resource).' published' : 'Moved to draft',
            duration: 4000,
            variant: 'success',
        );
    }

    public function getItemsProperty()
    {
        $model = CmsContent::model($this->resource);
        $title = CmsContent::titleField($this->resource);

        return $model::query()
            ->when($this->search, fn (Builder $query) => $query->where(fn (Builder $q) => $q->where($title, 'like', "%{$this->search}%")->orWhere('slug', 'like', "%{$this->search}%")))
            ->when($this->status, fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->resource === 'blog', fn (Builder $query) => $query->with('author'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getHeadingProperty(): string { return match ($this->resource) { 'pages' => 'Pages', 'services' => 'Services', 'projects' => 'Projects', 'blog' => 'Blog', }; }
    public function getDescriptionProperty(): string { return match ($this->resource) { 'pages' => 'Build and publish the pages that shape your public website.', 'services' => 'Manage the services Rexxsol presents to prospective clients.', 'projects' => 'Curate your portfolio and highlight delivered work.', 'blog' => 'Plan, write, and publish the Rexxsol editorial voice.', }; }
}; ?>

@php($titleField = CmsContent::titleField($resource))

<div class="rex-pages-page mx-auto w-full space-y-5">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#64748b]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:text-[#000080]">Dashboard</a>
        <span>/</span>
        <span class="text-[#172033]">{{ $this->heading }}</span>
    </nav>

    <section class="rex-pages w-full rounded-[12px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)]">
        <div class="rex-pages-toolbar border-b border-[#eef1f6]">
            <div class="rex-pages-toolbar-left">
                <div class="relative">
                    <select wire:model.live="bulkAction" class="appearance-none border border-[#dbe2ed] bg-white px-3 py-2.5 pr-9 text-sm text-[#172033] outline-none transition focus:border-[#000080] focus:ring-2 focus:ring-[#000080]/10">
                        <option value="">Bulk Actions</option>
                        @can(CmsPermissions::resourcePermission($resource, 'publish'))
                        <option value="publish">Publish</option>
                        @endcan
                        @can(CmsPermissions::resourcePermission($resource, 'update'))
                        <option value="draft">Move to Draft</option>
                        @endcan
                        @can(CmsPermissions::resourcePermission($resource, 'delete'))
                        <option value="delete">Delete</option>
                        @endcan
                    </select>
                    <flux:icon name="chevron-down" class="pointer-events-none absolute right-3 top-3 size-4 text-[#64748b]" />
                </div>
                <button type="button" wire:click="applyBulkAction" wire:loading.attr="disabled" wire:target="applyBulkAction" @disabled($bulkAction === '' || empty($selected)) class="inline-flex items-center justify-center rounded-[5px] border border-[#dbe2ed] bg-white px-3 py-2.5 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc] disabled:cursor-not-allowed disabled:opacity-50">
                    Apply
                </button>

                <div class="rex-pages-filter" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" @click="open = !open" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#dbe2ed] bg-white px-3 py-2.5 text-sm font-medium text-[#172033] transition hover:border-[#c7d1de] hover:bg-[#f7f9fc]">
                        <flux:icon name="funnel" class="size-4" />
                        Filters
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" class="rex-pages-filter-popover" x-cloak>
                        <div class="space-y-3 p-3">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#64748b]">Status</label>
                                <select wire:model.live="status" class="w-full border border-[#dbe2ed] bg-white px-2.5 py-2 text-sm text-[#172033] outline-none focus:border-[#000080]">
                                    <option value="">All</option>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-between border-t border-[#eef1f6] bg-[#f8fafc] px-3 py-2.5">
                            <button type="button" wire:click="$set('status', '')" @click="open = false" class="text-sm font-medium text-[#64748b] transition hover:text-[#172033]">Reset</button>
                            <button type="button" @click="open = false" class="rounded-lg bg-[#2563eb] px-2.5 py-1.5 text-sm font-semibold text-white">Apply</button>
                        </div>
                    </div>
                </div>

                <div class="rex-pages-search">
                    <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-3 size-4 text-[#94a3b8]" />
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search..." class="w-full border border-[#dbe2ed] bg-white py-2.5 pl-10 pr-3 text-sm text-[#172033] outline-none transition focus:border-[#000080] focus:ring-2 focus:ring-[#000080]/10" />
                </div>
            </div>

            <div class="rex-pages-toolbar-right">
                @can(CmsPermissions::resourcePermission($resource, 'create'))
                <a href="{{ route("admin.{$resource}.create") }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-[5px] bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]"><flux:icon name="plus" class="size-4" /> Create</a>
                @endcan
                <button type="button" wire:click="$refresh" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#dbe2ed] bg-white px-3 py-2.5 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc]"><flux:icon name="arrow-path" class="size-4" /> Reload</button>
            </div>
        </div>

        <div wire:loading.flex wire:target="search,status,bulkAction" class="items-center gap-2 border-b border-[#eef1f6] bg-[#f7f9fc] px-5 py-3 text-xs font-medium text-[#64748b]">
            <span class="size-3 animate-spin rounded-full border-2 border-[#000080] border-t-transparent"></span>
            Updating results...
        </div>

        @if ($this->items->count())
            <div class="rex-pages-table-wrap w-full overflow-x-auto">
                <table class="rex-pages-table w-full {{ $resource === 'pages' ? 'min-w-[1420px]' : 'min-w-[960px]' }} table-fixed border-separate border-spacing-0">
                    <thead class="bg-[#f7f9fc] text-left">
                        <tr>
                            <th class="w-[44px] border-b border-[#eef1f6] px-3 py-3 text-center align-middle">
                                <input type="checkbox" class="size-4 rounded border-[#c7d1de] accent-[#2563eb] align-middle" @checked($this->items->isNotEmpty() && count(array_intersect($this->items->pluck('id')->all(), $selected)) === $this->items->count()) wire:click="toggleSelectAll" />
                            </th>
                            <th class="w-[60px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
                                <button type="button" wire:click="sortBy('id')" class="inline-flex items-center gap-1">ID @if ($sortField === 'id')<span class="text-[#2563eb]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button>
                            </th>
                            <th class="border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
                                <button type="button" wire:click="sortBy('{{ $titleField }}')" class="inline-flex items-center gap-1">Name @if ($sortField === $titleField)<span class="text-[#2563eb]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button>
                            </th>
                            @if ($resource === 'pages')
                                <th class="w-[210px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Public URL</th>
                                <th class="w-[140px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Template</th>
                                <th class="w-[100px] border-b border-[#eef1f6] px-3 py-3 text-center text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Navbar</th>
                                <th class="w-[100px] border-b border-[#eef1f6] px-3 py-3 text-center text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Footer</th>
                                <th class="w-[90px] border-b border-[#eef1f6] px-3 py-3 text-center text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Order</th>
                            @else
                                <th class="w-[190px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">{{ $resource === 'services' ? 'Summary' : ($resource === 'projects' ? 'Project details' : 'Author') }}</th>
                                <th class="w-[150px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
                                    <button type="button" wire:click="sortBy('created_at')" class="inline-flex items-center gap-1">Created At @if ($sortField === 'created_at')<span class="text-[#2563eb]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button>
                                </th>
                            @endif
                            <th class="w-[120px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
                                <button type="button" wire:click="sortBy('status')" class="inline-flex items-center gap-1">Status @if ($sortField === 'status')<span class="text-[#2563eb]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button>
                            </th>
                            <th class="w-[120px] border-b border-[#eef1f6] px-3 py-3 text-right text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eef1f6]">
                        @foreach ($this->items as $item)
                            <tr class="bg-white transition hover:bg-[#f8fbff] even:bg-[#fcfdff]">
                                <td class="border-b border-[#eef1f6] px-3 py-3 text-center align-middle">
                                    <input type="checkbox" class="size-4 rounded border-[#c7d1de] accent-[#2563eb] align-middle" value="{{ $item->id }}" @checked(in_array($item->id, $selected, true)) wire:click="toggleSelected({{ $item->id }})" />
                                </td>
                                <td class="border-b border-[#eef1f6] px-3 py-3 text-sm font-medium text-[#172033] align-middle">{{ $item->id }}</td>
                                <td class="border-b border-[#eef1f6] px-3 py-3 align-middle">
                                    <a href="{{ $item->is_system && $item->slug === 'home' && Route::has('admin.homepage') ? route('admin.homepage') : route("admin.{$resource}.edit", $item->id) }}" wire:navigate class="text-sm font-semibold text-[#1464c4] transition hover:text-[#0d4f9c]">{{ CmsContent::label($item, $resource) }}</a>
                                </td>
                                @if ($resource === 'pages')
                                    <td class="border-b border-[#eef1f6] px-3 py-3 text-sm text-[#64748b] align-middle"><a class="block truncate text-[#1464c4] hover:underline" href="{{ $item->publicUrl() }}" target="_blank" rel="noopener noreferrer">{{ $item->publicUrl() }}</a></td>
                                    <td class="border-b border-[#eef1f6] px-3 py-3 text-sm text-[#64748b] align-middle">{{ $item->is_system ? ucwords(str_replace(['system-', '-'], ['', ' '], $item->template)).' system' : 'Default Page' }}</td>
                                    <td class="border-b border-[#eef1f6] px-3 py-3 text-center text-sm text-[#64748b] align-middle">{{ $item->is_system ? 'Managed' : ($item->show_in_navbar ? 'Yes' : 'No') }}</td>
                                    <td class="border-b border-[#eef1f6] px-3 py-3 text-center text-sm text-[#64748b] align-middle">{{ $item->is_system ? 'Managed' : ($item->show_in_footer ? 'Yes' : 'No') }}</td>
                                    <td class="border-b border-[#eef1f6] px-3 py-3 text-center text-sm text-[#64748b] align-middle">{{ $item->menu_order }}</td>
                                @else
                                    <td class="border-b border-[#eef1f6] px-3 py-3 text-sm text-[#64748b] align-middle"><span class="block truncate">{{ $resource === 'services' ? ($item->short_description ?: 'No summary') : ($resource === 'projects' ? trim(collect([$item->client, $item->category])->filter()->join(' · ')) ?: 'No project details' : ($item->author?->name ?: 'Unassigned')) }}</span></td>
                                    <td class="border-b border-[#eef1f6] px-3 py-3 text-sm text-[#64748b] align-middle">{{ $item->created_at?->format('Y-m-d') }}</td>
                                @endif
                                <td class="border-b border-[#eef1f6] px-3 py-3 align-middle">
                                    @can(CmsPermissions::resourcePermission($resource, 'publish'))
                                        @if ($resource === 'pages' && $item->is_system)
                                            <span class="rex-pages-status inline-flex items-center rounded-[6px] px-2 py-0.5 text-[12px] font-semibold rex-pages-status--published">Published</span>
                                        @else
                                        <button type="button" wire:click="togglePublish({{ $item->id }})" wire:loading.attr="disabled" wire:target="togglePublish({{ $item->id }})" class="rex-pages-status inline-flex items-center rounded-[6px] px-2 py-0.5 text-[12px] font-semibold transition hover:opacity-85 disabled:cursor-wait disabled:opacity-60 {{ $item->status === 'published' ? 'rex-pages-status--published' : 'rex-pages-status--draft' }}" aria-label="Toggle status for {{ CmsContent::label($item, $resource) }}">
                                            {{ ucfirst($item->status ?? 'draft') }}
                                        </button>
                                        @endif
                                    @else
                                        <span class="rex-pages-status inline-flex items-center rounded-[6px] px-2 py-0.5 text-[12px] font-semibold {{ $item->status === 'published' ? 'rex-pages-status--published' : 'rex-pages-status--draft' }}">
                                            {{ ucfirst($item->status ?? 'draft') }}
                                        </span>
                                    @endcan
                                </td>
                                <td class="border-b border-[#eef1f6] px-3 py-3 text-right align-middle">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($resource === 'pages')
                                            <a href="{{ $item->is_system ? $item->publicUrl() : route('admin.pages.preview', $item->id) }}" target="_blank" rel="noopener noreferrer" class="inline-flex size-8 items-center justify-center rounded-[4px] border border-[#dbe2ed] bg-white text-[#2563eb] transition hover:bg-[#f7f9fc]" aria-label="Preview {{ CmsContent::label($item, $resource) }}">
                                                <flux:icon name="eye" class="size-4" />
                                            </a>
                                        @endif
                                        @can(CmsPermissions::resourcePermission($resource, 'update'))
                                        <a href="{{ $item->is_system && $item->slug === 'home' && Route::has('admin.homepage') ? route('admin.homepage') : route("admin.{$resource}.edit", $item->id) }}" wire:navigate class="rex-pages-action rex-pages-action--edit inline-flex size-8 items-center justify-center rounded-[4px] border-0 text-white transition" aria-label="Edit {{ CmsContent::label($item, $resource) }}">
                                            <flux:icon name="pencil-square" class="size-4" />
                                        </a>
                                        @endcan
                                        @can(CmsPermissions::resourcePermission($resource, 'delete'))
                                            @if (! ($resource === 'pages' && $item->is_system))
                                            <button type="button" wire:click="confirmDelete({{ $item->id }})" class="rex-pages-action rex-pages-action--delete inline-flex size-8 items-center justify-center rounded-[4px] border-0 text-white transition" aria-label="Delete {{ CmsContent::label($item, $resource) }}">
                                                <flux:icon name="trash" class="size-4" />
                                            </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-[#eef1f6] px-4 py-3 text-sm text-[#64748b] sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-1.5"><flux:icon name="globe-alt" class="size-4" /> Show from {{ $this->items->firstItem() }} to {{ $this->items->lastItem() }} in <span class="rounded-[5px] bg-[#6c84a4] px-1.5 py-0.5 font-semibold text-white">{{ $this->items->total() }}</span> records</div>
                <div>{{ $this->items->onEachSide(1)->links() }}</div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center px-6 py-20 text-center">
                <span class="flex size-14 items-center justify-center rounded-full bg-[#eef4ff] text-[#2563eb]"><flux:icon name="document-text" class="size-7" /></span>
                <h3 class="mt-5 text-lg font-bold text-[#172033]">{{ $search ? 'No '.$this->heading.' match your current search.' : 'No '.$this->heading.' found.' }}</h3>
                @can('access-admin')
                    @if (Route::has("admin.{$resource}.create"))
                        <a href="{{ route("admin.{$resource}.create") }}" wire:navigate class="mt-5 inline-flex items-center justify-center rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]"><flux:icon name="plus" class="size-4" /> Create {{ CmsContent::singular($resource) }}</a>
                    @endif
                @endcan
            </div>
        @endif
    </section>

    @if ($deletingId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#172033]/45 px-4" role="dialog" aria-modal="true">
            <div class="w-full max-w-md rounded-xl border border-[#e5eaf2] bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-[#172033]">Delete this page?</h2>
                        <p class="mt-2 text-sm text-[#64748b]">This action cannot be undone.</p>
                    </div>
                    <button type="button" wire:click="$set('deletingId', null)" class="text-[#64748b] transition hover:text-[#172033]" aria-label="Close confirmation">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('deletingId', null)" class="rounded-lg border border-[#dbe2ed] bg-white px-4 py-2 text-sm font-semibold text-[#64748b]">Cancel</button>
                    <button type="button" wire:click="delete" class="rounded-lg bg-[#dc2626] px-4 py-2 text-sm font-semibold text-white">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .rex-pages-toolbar {
        width: 100%;
        min-height: 64px;
        padding: 14px 16px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
    }

    .rex-pages-toolbar-left {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .rex-pages-toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .rex-pages-filter {
        position: relative;
    }

    .rex-pages-filter-popover {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 50;
        width: 220px;
        overflow: hidden;
        border: 1px solid #e5eaf2;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(23, 32, 51, 0.12);
    }

    .rex-pages-search {
        position: relative;
        width: 280px;
        min-width: 280px;
        flex: 0 0 280px;
    }

    .rex-pages-status--published {
        background-color: #2eb84d;
        color: #fff;
    }

    .rex-pages-status--draft {
        background-color: #fef3c7;
        color: #b45309;
    }

    .rex-pages-action--edit {
        background-color: #2878d4;
    }

    .rex-pages-action--edit:hover {
        background-color: #1d63b5;
    }

    .rex-pages-action--delete {
        background-color: #dc3545;
    }

    .rex-pages-action--delete:hover {
        background-color: #bb2d3b;
    }

    @media (max-width: 1023px) {
        .rex-pages-toolbar {
            flex-wrap: wrap;
            display: flex;
            align-items: flex-start;
        }

        .rex-pages-toolbar-left,
        .rex-pages-toolbar-right {
            width: 100%;
            justify-content: flex-start;
        }

        .rex-pages-toolbar-left {
            flex-wrap: wrap;
        }

        .rex-pages-toolbar-right {
            margin-left: 0;
        }

        .rex-pages-search {
            width: 100%;
            min-width: 0;
            max-width: none;
            flex: 1 1 220px;
        }
    }
</style>
