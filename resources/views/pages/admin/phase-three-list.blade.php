<?php

use App\Support\CmsContent;
use App\Support\CmsPermissions;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] #[Title('Content management')] class extends Component {
    use WithPagination;

    public string $resource = 'team';
    #[Url(as: 'q')] public string $search = '';
    #[Url] public string $status = '';
    public string $sortField = 'sort_order';
    public string $sortDirection = 'asc';
    public string $bulkAction = '';
    public array $selected = [];
    public ?int $deletingId = null;

    public function mount(string $resource): void
    {
        abort_unless(in_array($resource, ['team', 'testimonials', 'faqs'], true), 404);
        $this->resource = $resource;
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($resource, 'view')), 403);
    }

    public function updatedSearch(): void { $this->resetPage(); $this->selected = []; }
    public function updatedStatus(): void { $this->resetPage(); $this->selected = []; }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'sort_order', 'created_at', 'status', CmsContent::titleField($this->resource)], true)) return;
        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function toggleSelectAll(): void
    {
        $visible = $this->items->pluck('id')->all();
        $this->selected = count(array_intersect($visible, $this->selected)) === count($visible)
            ? array_values(array_diff($this->selected, $visible))
            : array_values(array_unique([...$this->selected, ...$visible]));
    }

    public function toggleSelected(int $id): void
    {
        $this->selected = in_array($id, $this->selected, true) ? array_values(array_diff($this->selected, [$id])) : [...$this->selected, $id];
    }

    public function applyBulkAction(): void
    {
        if ($this->bulkAction === '' || empty($this->selected)) return;
        $model = CmsContent::model($this->resource);
        if ($this->bulkAction === 'publish') {
            abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'publish')), 403);
            $model::whereIn('id', $this->selected)->update(['status' => 'published', 'published_at' => now()]);
        } elseif ($this->bulkAction === 'draft') {
            abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'update')), 403);
            $model::whereIn('id', $this->selected)->update(['status' => 'draft', 'published_at' => null]);
        } elseif ($this->bulkAction === 'delete') {
            abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'delete')), 403);
            $model::whereIn('id', $this->selected)->delete();
        } else {
            abort(422, 'Unsupported bulk action.');
        }
        $this->selected = [];
        $this->bulkAction = '';
        Flux::toast(variant: 'success', heading: 'Bulk action completed', text: 'The selected records were updated successfully.');
    }

    public function confirmDelete(int $id): void { abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'delete')), 403); $this->deletingId = $id; }
    public function delete(): void { abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'delete')), 403); CmsContent::model($this->resource)::findOrFail($this->deletingId)->delete(); $this->deletingId = null; Flux::toast(variant: 'success', text: CmsContent::singular($this->resource).' deleted successfully.'); }
    public function togglePublish(int $id): void { abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'publish')), 403); $model = CmsContent::model($this->resource)::findOrFail($id); $model->status = $model->status === 'published' ? 'draft' : 'published'; $model->published_at = $model->status === 'published' ? now() : null; $model->save(); Flux::toast(variant: 'success', text: CmsContent::singular($this->resource).' status updated.'); }

    public function getItemsProperty()
    {
        $model = CmsContent::model($this->resource);
        $field = CmsContent::titleField($this->resource);
        return $model::query()->when($this->search, fn (Builder $q) => $q->where(fn (Builder $query) => $query->where($field, 'like', "%{$this->search}%")->orWhere('category', 'like', "%{$this->search}%")))->when($this->status, fn (Builder $q) => $q->where('status', $this->status))->orderBy($this->sortField, $this->sortDirection)->paginate(10);
    }

    public function getHeadingProperty(): string { return ['team' => 'Team', 'testimonials' => 'Testimonials', 'faqs' => 'FAQs'][$this->resource]; }
    public function getDescriptionProperty(): string { return ['team' => 'Present the people behind Rexxsol.', 'testimonials' => 'Manage client voices and social proof.', 'faqs' => 'Keep common questions clear and useful.'][$this->resource]; }
    public function label($item): string { return (string) $item->{CmsContent::titleField($this->resource)}; }
}; ?>

<div class="mx-auto w-full space-y-5">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#64748b]"><a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-[#000080]">Dashboard</a><span>/</span><span class="text-[#172033]">{{ $this->heading }}</span></nav>
    <section class="w-full rounded-[12px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)]">
        <div class="flex flex-col gap-3 border-b border-[#eef1f6] p-4 lg:flex-row lg:items-center lg:justify-between"><div class="flex min-w-0 flex-wrap items-center gap-2"><select wire:model.live="bulkAction" class="border border-[#dbe2ed] bg-white px-3 py-2.5 text-sm"><option value="">Bulk Actions</option><option value="publish">Publish</option><option value="draft">Move to Draft</option><option value="delete">Delete</option></select><button type="button" wire:click="applyBulkAction" @disabled(!$bulkAction || empty($selected)) class="border border-[#dbe2ed] bg-white px-3 py-2.5 text-sm font-medium disabled:opacity-50">Apply</button><select wire:model.live="status" class="border border-[#dbe2ed] bg-white px-3 py-2.5 text-sm"><option value="">All statuses</option><option value="draft">Draft</option><option value="published">Published</option></select><input wire:model.live.debounce.300ms="search" type="search" placeholder="Search {{ strtolower($this->heading) }}..." class="min-w-[220px] flex-1 border border-[#dbe2ed] px-3 py-2.5 text-sm lg:max-w-[300px]" /></div><div class="flex gap-2"><a href="{{ route("admin.{$resource}.create") }}" wire:navigate class="inline-flex items-center gap-2 rounded-[5px] bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white"><flux:icon name="plus" class="size-4" /> Create</a><button type="button" wire:click="$refresh" class="inline-flex items-center gap-2 rounded-lg border border-[#dbe2ed] bg-white px-3 py-2.5 text-sm font-medium"><flux:icon name="arrow-path" class="size-4" /> Reload</button></div></div>
        <div wire:loading.flex wire:target="search,status,bulkAction" class="items-center gap-2 border-b border-[#eef1f6] bg-[#f7f9fc] px-5 py-3 text-xs text-[#64748b]">Updating results...</div>
        @if ($this->items->count())
            <div class="w-full overflow-x-auto"><table class="w-full min-w-[850px] table-fixed border-separate border-spacing-0"><thead class="bg-[#f7f9fc] text-left"><tr><th class="w-[44px] border-b border-[#eef1f6] px-3 py-3 text-center"><input type="checkbox" wire:click="toggleSelectAll" @checked($this->items->isNotEmpty() && count(array_intersect($this->items->pluck('id')->all(), $selected)) === $this->items->count()) /></th><th class="border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]"><button wire:click="sortBy('{{ CmsContent::titleField($resource) }}')">{{ $this->heading === 'FAQs' ? 'Question' : 'Name' }}</button></th><th class="w-[190px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">{{ $resource === 'faqs' ? 'Category' : ($resource === 'team' ? 'Designation' : 'Company') }}</th><th class="w-[120px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]"><button wire:click="sortBy('status')">Status</button></th><th class="w-[90px] border-b border-[#eef1f6] px-3 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]"><button wire:click="sortBy('sort_order')">Order</button></th><th class="w-[120px] border-b border-[#eef1f6] px-3 py-3 text-right text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Operations</th></tr></thead><tbody>@foreach ($this->items as $item)<tr class="bg-white even:bg-[#fcfdff] hover:bg-[#f8fbff]"><td class="border-b border-[#eef1f6] px-3 py-3 text-center"><input type="checkbox" wire:click="toggleSelected({{ $item->id }})" @checked(in_array($item->id, $selected, true)) /></td><td class="border-b border-[#eef1f6] px-3 py-3"><a href="{{ route("admin.{$resource}.edit", $item->id) }}" wire:navigate class="block truncate text-sm font-semibold text-[#1464c4]">{{ $this->label($item) }}</a>@if ($resource === 'testimonials')<p class="mt-1 truncate text-xs text-[#64748b]">{{ $item->quote }}</p>@endif</td><td class="border-b border-[#eef1f6] px-3 py-3 text-sm text-[#64748b]">{{ $resource === 'team' ? $item->designation : ($item->category ?? $item->company ?? 'Uncategorized') }}</td><td class="border-b border-[#eef1f6] px-3 py-3"><span class="inline-flex rounded-[6px] px-2 py-0.5 text-[12px] font-semibold {{ $item->status === 'published' ? 'bg-[#2eb84d] text-white' : 'bg-[#fef3c7] text-[#b45309]' }}">{{ ucfirst($item->status) }}</span></td><td class="border-b border-[#eef1f6] px-3 py-3 text-sm text-[#64748b]">{{ $item->sort_order }}</td><td class="border-b border-[#eef1f6] px-3 py-3 text-right"><div class="flex justify-end gap-2"><a href="{{ route("admin.{$resource}.edit", $item->id) }}" wire:navigate class="inline-flex size-8 items-center justify-center bg-[#2878d4] text-white" aria-label="Edit {{ $this->label($item) }}"><flux:icon name="pencil-square" class="size-4" /></a>@can(CmsPermissions::resourcePermission($resource, 'publish'))<button wire:click="togglePublish({{ $item->id }})" class="inline-flex size-8 items-center justify-center bg-[#64748b] text-white" aria-label="Toggle status"><flux:icon name="arrow-path" class="size-4" /></button>@endcan @can(CmsPermissions::resourcePermission($resource, 'delete'))<button wire:click="confirmDelete({{ $item->id }})" class="inline-flex size-8 items-center justify-center bg-[#dc3545] text-white" aria-label="Delete {{ $this->label($item) }}"><flux:icon name="trash" class="size-4" /></button>@endcan</div></td></tr>@endforeach</tbody></table></div><div class="flex flex-col gap-3 border-t border-[#eef1f6] px-4 py-3 text-sm text-[#64748b] sm:flex-row sm:items-center sm:justify-between"><span>Showing {{ $this->items->firstItem() }} to {{ $this->items->lastItem() }} of {{ $this->items->total() }} records</span>{{ $this->items->onEachSide(1)->links() }}</div>
        @else
            <div class="flex flex-col items-center justify-center px-6 py-20 text-center"><flux:icon name="document-plus" class="size-8 text-[#2563eb]" /><h3 class="mt-4 text-lg font-bold text-[#172033]">No {{ strtolower($this->heading) }} found.</h3><p class="mt-2 text-sm text-[#64748b]">Create your first {{ strtolower(CmsContent::singular($resource)) }} to get started.</p></div>
        @endif
    </section>
    @if ($deletingId)<div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#172033]/45 px-4" role="dialog" aria-modal="true"><div class="w-full max-w-md rounded-xl border border-[#e5eaf2] bg-white p-6 shadow-xl"><h2 class="text-lg font-bold text-[#172033]">Delete this {{ strtolower(CmsContent::singular($resource)) }}?</h2><p class="mt-2 text-sm text-[#64748b]">This action cannot be undone.</p><div class="mt-6 flex justify-end gap-3"><button wire:click="$set('deletingId', null)" class="border border-[#dbe2ed] px-4 py-2 text-sm">Cancel</button><button wire:click="delete" class="bg-[#dc2626] px-4 py-2 text-sm font-semibold text-white">Delete</button></div></div></div>@endif
</div>
