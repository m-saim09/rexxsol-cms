<?php

use App\Models\Media;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] #[Title('Media Library')] class extends Component {
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $source = '';

    #[Url]
    public string $sort = 'newest';

    #[Url]
    public string $layout = 'grid';

    public array $uploads = [];
    public array $uploadResults = [];
    public bool $uploadOpen = false;

    #[Locked]
    public ?int $selectedId = null;

    public string $title = '';
    public string $alt_text = '';

    #[Locked]
    public ?int $deletingId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedType(): void { $this->resetPage(); }
    public function updatedSource(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }
    public function updatedLayout(): void { if (! in_array($this->layout, ['grid', 'list'], true)) $this->layout = 'grid'; }

    public function openUpload(): void
    {
        abort_unless(auth()->user()->can('media.upload'), 403);
        $this->uploadResults = [];
        $this->uploadOpen = true;
    }

    public function closeUpload(): void
    {
        $this->uploadOpen = false;
        $this->uploads = [];
    }

    public function updatedUploads(): void
    {
        abort_unless(auth()->user()->can('media.upload'), 403);
        $this->uploadResults = [];
        foreach ($this->uploads as $upload) {
            $filename = $upload->getClientOriginalName();
            $validation = Validator::make(['file' => $upload], [
                'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            ]);
            if ($validation->fails()) {
                $this->uploadResults[] = ['name' => $filename, 'ok' => false, 'message' => $validation->errors()->first('file')];
                continue;
            }

            try {
                Media::fromUpload($upload, [
                    'uploaded_by' => auth()->id(),
                    'disk' => 'public',
                    'source_module' => 'Media Library',
                    'source_field' => 'Direct upload',
                    'source_record_name' => null,
                    'source_label' => 'Direct upload',
                ]);
                $this->uploadResults[] = ['name' => $filename, 'ok' => true, 'message' => 'Uploaded and registered.'];
            } catch (ValidationException $exception) {
                $this->uploadResults[] = ['name' => $filename, 'ok' => false, 'message' => $exception->validator->errors()->first() ?: $exception->getMessage()];
            }
        }

        $this->uploads = [];
        if (collect($this->uploadResults)->contains(fn (array $result): bool => $result['ok'])) {
            $this->resetPage();
            if (collect($this->uploadResults)->every(fn (array $result): bool => $result['ok'])) {
                Flux::toast(variant: 'success', text: 'Images uploaded and registered in Media Library.');
            }
        }
    }

    public function select(int $id): void
    {
        abort_unless(auth()->user()->can('media.view'), 403);
        $media = Media::query()->where('disk', 'public')->findOrFail($id);
        $this->selectedId = $media->id;
        $this->title = $media->title ?? '';
        $this->alt_text = $media->alt_text ?? '';
    }

    public function closeDetails(): void
    {
        $this->selectedId = null;
        $this->resetValidation();
    }

    public function getSelectedProperty(): ?Media
    {
        return $this->selectedId ? Media::query()->with('uploader')->where('disk', 'public')->find($this->selectedId) : null;
    }

    public function saveMetadata(): void
    {
        abort_unless(auth()->user()->can('media.update'), 403);
        $this->validate(['title' => ['required', 'string', 'max:255'], 'alt_text' => ['nullable', 'string', 'max:255']]);
        $media = Media::query()->where('disk', 'public')->findOrFail($this->selectedId);
        $media->title = trim($this->title);
        $media->title_source = 'manual';
        $altText = trim($this->alt_text);
        if ($altText !== trim((string) $media->alt_text)) {
            $media->alt_text = $altText === '' ? null : $altText;
            $media->alt_text_source = 'manual';
            $media->alt_text_status = 'reviewed';
        }
        $media->save();
        Flux::toast(variant: 'success', text: 'Media details saved.');
    }

    public function useTitleAsAltText(): void
    {
        abort_unless(auth()->user()->can('media.update'), 403);
        $media = Media::query()->where('disk', 'public')->findOrFail($this->selectedId);
        $this->alt_text = $media->title ?? '';
        $media->alt_text = $this->alt_text === '' ? null : $this->alt_text;
        $media->alt_text_source = 'manual';
        $media->alt_text_status = 'reviewed';
        $media->save();
        Flux::toast(variant: 'success', text: 'Title saved as the reviewed alt text.');
    }

    public function confirmDelete(int $id): void
    {
        abort_unless(auth()->user()->can('media.delete'), 403);
        $this->deletingId = Media::query()->where('disk', 'public')->findOrFail($id)->id;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function delete(): void
    {
        abort_unless(auth()->user()->can('media.delete'), 403);
        $media = Media::query()->where('disk', 'public')->findOrFail($this->deletingId);
        abort_if($media->currentUsage() !== [], 422, 'This image is still in use. Remove its references before deleting it.');

        $path = Media::normalizePath($media->path);
        if (Storage::disk($media->disk)->exists($path) && ! Storage::disk($media->disk)->delete($path)) {
            throw new \RuntimeException('The media file could not be deleted from storage.');
        }
        $media->delete();
        $this->deletingId = null;
        if ($this->selectedId === $media->id) $this->selectedId = null;
        Flux::toast(variant: 'success', text: 'Media deleted successfully.');
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->type = '';
        $this->source = '';
        $this->sort = 'newest';
        $this->resetPage();
    }

    public function refreshItems(): void
    {
        $this->resetPage();
    }

    public function getItemsProperty()
    {
        return Media::query()
            ->where('disk', 'public')
            ->when($this->search !== '', fn ($query) => $query->where(fn ($search) => $search
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('original_name', 'like', '%'.$this->search.'%')
                ->orWhere('alt_text', 'like', '%'.$this->search.'%')))
            ->when($this->type !== '', fn ($query) => $query->where('mime_type', $this->type))
            ->when($this->source === 'unknown', fn ($query) => $query->whereNull('source_module')->whereNull('source_field'))
            ->when($this->source !== '' && $this->source !== 'unknown', fn ($query) => $query->where('source_module', $this->source))
            ->when(in_array($this->sort, ['oldest', 'newest'], true), fn ($query) => $query
                ->orderByRaw('CASE WHEN uploaded_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('uploaded_at', $this->sort === 'oldest' ? 'asc' : 'desc')
                ->orderBy('id', $this->sort === 'oldest' ? 'asc' : 'desc'))
            ->when($this->sort === 'name', fn ($query) => $query->orderBy('title')->orderBy('original_name'))
            ->when($this->sort === 'size', fn ($query) => $query->orderByDesc('size'))
            ->paginate(20);
    }

    public function getUsageCountsProperty(): array
    {
        return Media::usageCountsFor($this->items->getCollection());
    }

    public function getSourcesProperty()
    {
        return Media::query()->where('disk', 'public')->whereNotNull('source_module')->distinct()->orderBy('source_module')->pluck('source_module');
    }
}; ?>

<div class="mx-auto max-w-[1500px] space-y-5">
    @php($items = $this->items)
    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs font-medium text-[#64748b]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#2563eb] hover:underline">Dashboard</a>
        <span aria-hidden="true">/</span><span>Content</span><span aria-hidden="true">/</span>
        <span class="text-[#172033]" aria-current="page">Media Library</span>
    </nav>

    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-[#172033]">Media Library</h1><p class="mt-1 text-sm text-[#64748b]">Manage and reuse your website images.</p></div>
        @can('media.upload')
            <button type="button" wire:click="openUpload" class="inline-flex h-10 items-center justify-center gap-2 rounded-[8px] bg-[#2563eb] px-4 text-sm font-semibold text-white transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/30">
                <flux:icon name="arrow-up-tray" class="size-4" /> Upload images
            </button>
        @endcan
    </header>

    <section class="rounded-[8px] border border-[#e2e8f0] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)]" aria-label="Media filters">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_160px_190px_160px_auto]">
            <label class="relative block">
                <span class="sr-only">Search media</span><flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#94a3b8]" />
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search title, filename, alt text" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] pl-9 pr-3 text-sm outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10" />
            </label>
            <label><span class="sr-only">File type</span><select wire:model.live="type" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10"><option value="">All file types</option><option value="image/jpeg">JPEG</option><option value="image/png">PNG</option><option value="image/webp">WebP</option><option value="image/gif">GIF</option></select></label>
            <label><span class="sr-only">Upload source</span><select wire:model.live="source" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10"><option value="">All upload sources</option><option value="unknown">Original source unknown</option>@foreach ($this->sources as $sourceName)<option value="{{ $sourceName }}">{{ $sourceName }}</option>@endforeach</select></label>
            <label><span class="sr-only">Sort media</span><select wire:model.live="sort" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10"><option value="newest">Newest</option><option value="oldest">Oldest</option><option value="name">Name</option><option value="size">File size</option></select></label>
            <div class="flex items-center justify-between gap-2 md:col-span-2 xl:col-span-1 xl:justify-end">
                <div class="inline-flex h-10 rounded-[6px] border border-[#dbe2ed] p-1" role="group" aria-label="Media view">
                    <button type="button" wire:click="$set('layout','grid')" aria-pressed="{{ $layout === 'grid' ? 'true' : 'false' }}" aria-label="Grid view" class="rounded px-2 {{ $layout === 'grid' ? 'bg-[#eff6ff] text-[#2563eb]' : 'text-[#64748b]' }}"><flux:icon name="squares-2x2" class="size-4" /></button>
                    <button type="button" wire:click="$set('layout','list')" aria-pressed="{{ $layout === 'list' ? 'true' : 'false' }}" aria-label="List view" class="rounded px-2 {{ $layout === 'list' ? 'bg-[#eff6ff] text-[#2563eb]' : 'text-[#64748b]' }}"><flux:icon name="bars-3" class="size-4" /></button>
                </div>
                <button type="button" wire:click="refreshItems" aria-label="Refresh media" class="inline-flex h-10 items-center gap-2 rounded-[6px] border border-[#dbe2ed] px-3 text-sm font-medium text-[#475569] hover:bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/20"><flux:icon name="arrow-path" class="size-4" /><span class="hidden sm:inline">Refresh</span></button>
                @if ($search !== '' || $type !== '' || $source !== '' || $sort !== 'newest')
                    <button type="button" wire:click="clearFilters" class="h-10 px-2 text-sm font-medium text-[#2563eb] hover:underline">Clear filters</button>
                @endif
            </div>
        </div>
    </section>

    <div wire:loading.delay class="rounded-[8px] border border-[#dbeafe] bg-white p-4 text-sm text-[#64748b]" role="status">Loading media…</div>

    @if ($items->count())
        @if ($layout === 'grid')
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" wire:loading.class="opacity-60">
                @foreach ($items as $item)
                    <article wire:key="media-card-{{ $item->id }}" class="group relative overflow-hidden rounded-[8px] border border-[#e2e8f0] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)] transition hover:border-[#bfdbfe] hover:shadow-md">
                        <button type="button" wire:click="select({{ $item->id }})" class="block w-full text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#2563eb]" aria-label="View details for {{ $item->title ?: 'Untitled image' }}">
                            <div class="relative aspect-[4/3] bg-[linear-gradient(45deg,#e9edf3_25%,transparent_25%),linear-gradient(-45deg,#e9edf3_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#e9edf3_75%),linear-gradient(-45deg,transparent_75%,#e9edf3_75%)] bg-[length:16px_16px] bg-[position:0_0,0_8px,8px_-8px,-8px_0]">
                                <img loading="lazy" src="{{ Storage::disk($item->disk)->url($item->path) }}" alt="" class="h-full w-full object-contain p-2" x-data x-on:error="$el.classList.add('hidden'); $el.nextElementSibling.classList.remove('hidden')" />
                                <div class="hidden h-full w-full items-center justify-center text-sm text-[#64748b]"><flux:icon name="photo" class="mr-2 size-5" />Preview unavailable</div>
                            </div>
                            <div class="p-4">
                                <h2 class="truncate text-sm font-semibold text-[#172033]">{{ $item->title ?: 'Untitled image' }}</h2>
                                <p class="mt-1 text-xs text-[#64748b]">{{ strtoupper($item->extension) }} · {{ $item->width }} × {{ $item->height }} · {{ number_format($item->size / 1024, 1) }} KB</p>
                                <p class="mt-2 truncate text-[11px] text-[#64748b]">Uploaded from: {{ $item->sourceLabel() }}</p>
                                <p class="mt-1 text-[11px] text-[#64748b]">Used in {{ $this->usageCounts[$item->id] ?? 0 }} known locations</p>
                            </div>
                        </button>
                        @can('media.delete')
                            @if (($this->usageCounts[$item->id] ?? 0) === 0)
                                <button type="button" wire:click="confirmDelete({{ $item->id }})" aria-label="Delete {{ $item->title ?: 'Untitled image' }}" title="Delete image" class="absolute right-2 top-2 inline-flex size-9 items-center justify-center rounded-[6px] border border-white/70 bg-white/95 text-red-600 shadow-sm transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <flux:icon name="trash" class="size-4" />
                                </button>
                            @else
                                <button type="button" disabled aria-label="Cannot delete {{ $item->title ?: 'Untitled image' }} because it is in use" title="Remove this image from its {{ $this->usageCounts[$item->id] }} known locations before deleting it" class="absolute right-2 top-2 inline-flex size-9 cursor-not-allowed items-center justify-center rounded-[6px] border border-white/70 bg-white/95 text-slate-400 shadow-sm">
                                    <flux:icon name="trash" class="size-4" />
                                </button>
                            @endif
                        @endcan
                    </article>
                @endforeach
            </div>
        @else
            <div class="overflow-x-auto rounded-[8px] border border-[#e2e8f0] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="border-b border-[#e2e8f0] bg-[#f8fafc] text-xs font-semibold uppercase tracking-wide text-[#64748b]"><tr><th class="px-4 py-3">Thumbnail</th><th class="px-4 py-3">Title</th><th class="px-4 py-3">Uploaded from</th><th class="px-4 py-3">Usage</th><th class="px-4 py-3">Size</th><th class="px-4 py-3">Uploaded at</th><th class="px-4 py-3"><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody class="divide-y divide-[#eef2f7]">
                        @foreach ($items as $item)
                            <tr wire:key="media-row-{{ $item->id }}" class="hover:bg-[#f8fafc]">
                                <td class="px-4 py-3"><button type="button" wire:click="select({{ $item->id }})" aria-label="View details for {{ $item->title ?: 'Untitled image' }}" class="block size-14 rounded border border-[#e2e8f0] bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#2563eb]"><img loading="lazy" src="{{ Storage::disk($item->disk)->url($item->path) }}" alt="" class="size-full object-contain" /></button></td>
                                <td class="max-w-[240px] truncate px-4 py-3 font-semibold text-[#172033]">{{ $item->title ?: 'Untitled image' }}</td><td class="max-w-[220px] truncate px-4 py-3 text-xs text-[#64748b]">{{ $item->sourceLabel() }}</td>
                                <td class="px-4 py-3 text-xs text-[#64748b]">{{ $this->usageCounts[$item->id] ?? 0 }} known locations</td><td class="px-4 py-3 text-xs text-[#64748b]">{{ number_format($item->size / 1024, 1) }} KB</td><td class="whitespace-nowrap px-4 py-3 text-xs text-[#64748b]">{{ $item->uploaded_at?->format('M j, Y') ?? 'Unknown' }}</td>
                                <td class="px-4 py-3 text-right"><button type="button" wire:click="select({{ $item->id }})" class="rounded px-3 py-1.5 text-xs font-semibold text-[#2563eb] hover:bg-[#eff6ff] focus:outline-none focus:ring-2 focus:ring-[#2563eb]">Details</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <div>{{ $items->links() }}</div>
    @elseif ($search !== '' || $type !== '' || $source !== '')
        <section class="rounded-[8px] border border-[#e2e8f0] bg-white px-6 py-16 text-center"><flux:icon name="magnifying-glass" class="mx-auto size-8 text-[#94a3b8]" /><h2 class="mt-3 font-semibold text-[#172033]">No matching images</h2><p class="mt-1 text-sm text-[#64748b]">Try a different search or clear your filters.</p><button type="button" wire:click="clearFilters" class="mt-4 text-sm font-semibold text-[#2563eb] hover:underline">Clear filters</button></section>
    @else
        <section class="rounded-[8px] border border-dashed border-[#cbd5e1] bg-white px-6 py-16 text-center"><flux:icon name="photo" class="mx-auto size-9 text-[#2563eb]" /><h2 class="mt-3 text-base font-semibold text-[#172033]">Your Media Library is empty</h2><p class="mt-1 text-sm text-[#64748b]">Upload an image to begin building a shared library.</p>@can('media.upload')<button type="button" wire:click="openUpload" class="mt-4 rounded-[8px] bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8]">Upload images</button>@endcan</section>
    @endif

    @if ($uploadOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/45 p-4" role="presentation" x-data x-on:keydown.escape.window="$wire.closeUpload()">
            <section class="w-full max-w-2xl rounded-[10px] border border-[#e2e8f0] bg-white p-5 shadow-xl sm:p-6" role="dialog" aria-modal="true" aria-labelledby="upload-title" x-trap.inert.noscroll="true" x-init="$nextTick(() => $refs.uploadClose.focus())">
                <div class="flex items-start justify-between gap-4"><div><h2 id="upload-title" class="text-lg font-bold text-[#172033]">Upload images</h2><p class="mt-1 text-sm text-[#64748b]">JPG, PNG, WebP or GIF · Maximum 5 MB per image</p></div><button x-ref="uploadClose" type="button" wire:click="closeUpload" aria-label="Close upload dialog" class="rounded p-2 text-[#64748b] hover:bg-[#f1f5f9] focus:outline-none focus:ring-2 focus:ring-[#2563eb]"><flux:icon name="x-mark" class="size-5" /></button></div>
                <label for="media-upload" class="mt-5 flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-[8px] border-2 border-dashed border-[#bfdbfe] bg-[#f8fafc] p-6 text-center transition hover:bg-[#eff6ff]" x-on:dragover.prevent x-on:drop.prevent="$refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }))">
                    <flux:icon name="arrow-up-tray" class="size-7 text-[#2563eb]" /><span class="mt-2 text-sm font-semibold text-[#172033]">Drop images here or browse files</span><span class="mt-1 text-xs text-[#64748b]">Multiple images can be selected</span>
                    <input id="media-upload" x-ref="fileInput" wire:model="uploads" multiple type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only" />
                </label>
                <div wire:loading wire:target="uploads" class="mt-3 rounded-md bg-[#eff6ff] px-3 py-2 text-sm text-[#1d4ed8]" role="status">Uploading and validating selected images…</div>
                @if ($uploadResults)
                    <ul class="mt-4 max-h-48 space-y-2 overflow-y-auto" aria-live="polite">
                        @foreach ($uploadResults as $result)
                            <li class="flex gap-2 rounded-md border px-3 py-2 text-sm {{ $result['ok'] ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' }}"><span class="min-w-0 flex-1 break-all font-medium">{{ $result['name'] }}</span><span class="text-right">{{ $result['message'] }}</span></li>
                        @endforeach
                    </ul>
                @endif
                @error('uploads')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="mt-5 flex justify-end"><button type="button" wire:click="closeUpload" class="h-10 rounded-[6px] border border-[#dbe2ed] px-4 text-sm font-semibold text-[#475569] hover:bg-[#f8fafc]">Close</button></div>
            </section>
        </div>
    @endif

    @if ($selectedId && $this->selected)
        @php($selected = $this->selected)
        <div class="fixed inset-0 z-50 bg-[#0f172a]/45" role="presentation" x-data x-on:keydown.escape.window="$wire.closeDetails()">
            <aside class="absolute inset-y-0 right-0 flex w-full max-w-2xl flex-col overflow-y-auto bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="media-details-title" x-trap.inert.noscroll="true" x-init="$nextTick(() => $refs.detailsClose.focus())">
                <div class="flex items-start justify-between border-b border-[#e2e8f0] px-5 py-4 sm:px-6"><div><h2 id="media-details-title" class="text-lg font-bold text-[#172033]">Image details</h2><p class="mt-0.5 text-xs text-[#64748b]">Metadata updates do not change the stored image URL.</p></div><button x-ref="detailsClose" type="button" wire:click="closeDetails" aria-label="Close media details" class="rounded p-2 text-[#64748b] hover:bg-[#f1f5f9] focus:outline-none focus:ring-2 focus:ring-[#2563eb]"><flux:icon name="x-mark" class="size-5" /></button></div>
                <div class="flex-1 space-y-5 p-5 sm:p-6">
                    <div class="flex aspect-[16/10] items-center justify-center rounded-[8px] border border-[#e2e8f0] bg-[#f8fafc] p-3"><img src="{{ Storage::disk($selected->disk)->url($selected->path) }}" alt="" class="max-h-full max-w-full object-contain" /></div>
                    <form wire:submit="saveMetadata" class="space-y-4">
                        <label class="block text-sm font-semibold text-[#172033]">Title<input wire:model="title" type="text" maxlength="255" class="mt-1.5 h-10 w-full rounded-[6px] border border-[#dbe2ed] px-3 text-sm outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10" />@error('title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                        <label class="block text-sm font-semibold text-[#172033]">Alt text<textarea wire:model="alt_text" rows="3" maxlength="255" class="mt-1.5 w-full rounded-[6px] border border-[#dbe2ed] px-3 py-2 text-sm outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10"></textarea><span class="mt-1 block text-xs font-normal text-[#64748b]">Briefly describe what the image communicates.</span></label>
                        <div class="flex flex-wrap items-center justify-between gap-2"><span class="text-xs {{ $selected->alt_text_status === 'needs_description' ? 'font-semibold text-amber-700' : 'text-[#64748b]' }}">{{ $selected->alt_text_status === 'needs_description' ? 'Needs description' : ($selected->alt_text_status === 'reviewed' ? 'Manually reviewed' : 'Filename suggestion') }}</span>@can('media.update')<button type="button" wire:click="useTitleAsAltText" class="text-xs font-semibold text-[#2563eb] hover:underline">Use title as alt text</button>@endcan</div>
                        <label class="block text-sm font-semibold text-[#172033]">Original filename<input value="{{ $selected->original_name }}" readonly class="mt-1.5 h-10 w-full rounded-[6px] border border-[#e2e8f0] bg-[#f8fafc] px-3 text-sm text-[#64748b]" /></label>
                        <div class="grid grid-cols-2 gap-3 text-xs"><div class="rounded-md bg-[#f8fafc] p-3"><span class="block text-[#64748b]">File</span><strong class="mt-1 block text-[#172033]">{{ strtoupper($selected->extension) }} · {{ number_format($selected->size / 1024, 1) }} KB</strong></div><div class="rounded-md bg-[#f8fafc] p-3"><span class="block text-[#64748b]">Dimensions</span><strong class="mt-1 block text-[#172033]">{{ $selected->width }} × {{ $selected->height }}</strong></div></div>
                        <section class="rounded-[8px] border border-[#e2e8f0] p-4"><h3 class="text-xs font-bold uppercase tracking-wide text-[#64748b]">Uploaded from</h3><p class="mt-2 text-sm text-[#172033]">@if ($url = $selected->uploadSourceUrl())<a href="{{ $url }}" wire:navigate class="text-[#2563eb] hover:underline">{{ $selected->sourceLabel() }}</a>@else{{ $selected->sourceLabel() }}@endif</p></section>
                        <section class="rounded-[8px] border border-[#e2e8f0] p-4"><h3 class="text-xs font-bold uppercase tracking-wide text-[#64748b]">Used in</h3>@php($usage = $selected->currentUsage())@if ($usage)<ul class="mt-2 space-y-2">@foreach ($usage as $reference)<li class="text-sm text-[#172033]">@if ($reference['route'])<a href="{{ $reference['route'] }}" wire:navigate class="text-[#2563eb] hover:underline">{{ $reference['label'] }}</a>@else{{ $reference['label'] }}@endif</li>@endforeach</ul>@else<p class="mt-2 text-sm text-[#64748b]">No known CMS references.</p>@endif</section>
                        <div class="text-xs text-[#64748b]">Uploaded by {{ $selected->uploader?->name ?? 'Unknown user' }} · {{ $selected->uploaded_at?->format('M j, Y g:i A') ?? 'Upload date unknown' }}</div>
                        @if ($selected->disk === 'public')<div class="flex flex-wrap gap-3"><a href="{{ Storage::disk($selected->disk)->url($selected->path) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-[#2563eb] hover:underline">Open image URL</a><a href="{{ Storage::disk($selected->disk)->url($selected->path) }}" download="{{ $selected->original_name }}" class="text-xs font-semibold text-[#2563eb] hover:underline">Download</a></div>@endif
                    </form>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-[#e2e8f0] bg-white p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    @can('media.delete')<div>@if ($selected->currentUsage() === [])<button type="button" wire:click="confirmDelete({{ $selected->id }})" class="h-10 rounded-[6px] px-3 text-sm font-semibold text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300">Delete image</button>@else<button type="button" disabled title="Remove this image from its {{ count($selected->currentUsage()) }} known locations before deleting it" class="h-10 cursor-not-allowed rounded-[6px] px-3 text-sm font-semibold text-[#94a3b8]">Delete image · In use</button>@endif</div>@endcan
                    <div class="flex justify-end gap-2"><button type="button" wire:click="closeDetails" class="h-10 rounded-[6px] border border-[#dbe2ed] px-4 text-sm font-semibold text-[#475569] hover:bg-[#f8fafc]">Close</button>@can('media.update')<button type="button" wire:click="saveMetadata" wire:loading.attr="disabled" wire:target="saveMetadata" class="h-10 rounded-[6px] bg-[#2563eb] px-4 text-sm font-semibold text-white hover:bg-[#1d4ed8] disabled:opacity-50">Save changes</button>@endcan</div>
                </div>
            </aside>
        </div>
    @endif

    @if ($deletingId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#0f172a]/55 p-4" role="presentation" x-data x-on:keydown.escape.window="$wire.cancelDelete()">
            <section class="w-full max-w-md rounded-[10px] border border-[#e2e8f0] bg-white p-6 shadow-xl" role="alertdialog" aria-modal="true" aria-labelledby="delete-media-title">
                <h2 id="delete-media-title" class="text-lg font-bold text-[#172033]">Delete this image?</h2><p class="mt-2 text-sm text-[#64748b]">This permanently deletes the file. This action cannot be undone.</p>
                @error('media')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="mt-5 flex justify-end gap-2"><button type="button" wire:click="cancelDelete" class="h-10 rounded-[6px] border border-[#dbe2ed] px-4 text-sm font-semibold text-[#475569]">Cancel</button><button type="button" wire:click="delete" wire:loading.attr="disabled" class="h-10 rounded-[6px] bg-red-600 px-4 text-sm font-semibold text-white hover:bg-red-700">Delete image</button></div>
            </section>
        </div>
    @endif
</div>
