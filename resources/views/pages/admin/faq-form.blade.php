<?php

use App\Support\CmsContent;
use App\Support\CmsPermissions;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin')] #[Title('Edit FAQ')] class extends Component {
    public string $resource = 'faqs';
    public ?int $modelId = null;
    public string $question = '';
    public string $answer = '';
    public string $category = '';
    public string $status = 'draft';
    public int $sort_order = 0;

    public function mount(?string $model = null): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, $model ? 'update' : 'create')), 403);
        if ($model) {
            $this->fillFromModel(CmsContent::model($this->resource)::query()->findOrFail($model));
        }
    }

    protected function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function saveDraft(): void
    {
        $this->save(false);
    }

    public function saveAndExit(): void
    {
        $this->save(false, true);
    }

    public function publish(): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'publish')), 403);
        $this->save(true);
    }

    protected function save(bool $publish, bool $exit = false): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, $this->modelId ? 'update' : 'create')), 403);
        $this->validate($this->rules());
        $model = $this->modelId ? CmsContent::model($this->resource)::query()->findOrFail($this->modelId) : new (CmsContent::model($this->resource));
        $model->fill([
            'question' => $this->question,
            'answer' => $this->answer,
            'category' => $this->category,
            'sort_order' => $this->sort_order,
        ]);
        $model->status = $publish ? 'published' : 'draft';
        $model->published_at = $publish ? ($model->published_at ?: now()) : null;
        $model->save();
        $this->modelId = $model->id;

        if ($exit) {
            session()->flash('cms_toast', [
                'heading' => 'FAQ saved',
                'text' => 'Changes saved successfully. Returning to the FAQs.',
                'variant' => 'success',
                'duration' => 4000,
            ]);
            $this->redirectRoute('admin.faqs.index', navigate: true);

            return;
        }

        Flux::toast(variant: 'success', text: 'FAQ '.($publish ? 'published' : 'saved').' successfully.');
        $this->redirectRoute('admin.faqs.edit', $model->id, navigate: true);
    }

    protected function fillFromModel($model): void
    {
        foreach (['question', 'answer', 'category', 'sort_order'] as $field) {
            if (isset($model->{$field})) {
                $this->{$field} = $model->{$field};
            }
        }

        $this->modelId = $model->id;
        $this->status = $model->status;
    }
}; ?>
<div class="rex-content-form-root">
    <div class="rex-page-edit-page mx-auto w-full max-w-[1440px] space-y-5">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.faqs.index') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">FAQs</a>
            <span>/</span>
            <span class="text-[#172033]">{{ $modelId ? 'Edit' : 'Create' }} {{ $question ?: 'FAQ' }}</span>
        </nav>

        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#2563eb]">Detail</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#172033]">{{ $modelId ? 'Edit FAQ' : 'Create FAQ' }}</h1>
                <p class="mt-1 text-sm text-[#64748b]">Update the question, answer and publishing settings.</p>
            </div>
            <span class="rex-page-edit-state {{ $status === 'published' ? 'rex-page-edit-state--published' : 'rex-page-edit-state--draft' }}">{{ ucfirst($status) }}</span>
        </div>

        <form wire:submit.prevent="saveDraft" class="rex-page-edit-grid" style="display:grid;grid-template-columns:minmax(0,3fr) minmax(260px,1fr);gap:16px;align-items:start">
            <main class="space-y-4">
                <section class="rex-page-edit-card">
                    <div class="rex-page-edit-card-heading"><div><h2>Question and answer</h2><p>Write a clear response for visitors looking for quick answers.</p></div></div>
                    <div class="rex-page-edit-fields">
                        <div>
                            <label for="faq-question">Question <span>*</span></label>
                            <input id="faq-question" wire:model="question" type="text" placeholder="What would you like to know?" />
                            @error('question')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="faq-category">Category</label>
                            <input id="faq-category" wire:model="category" type="text" placeholder="General" />
                            @error('category')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <div class="rex-page-edit-editor-label"><label for="faq-answer">Answer <span>*</span></label><span>Public FAQ copy</span></div>
                            <textarea id="faq-answer" wire:model="answer" rows="16" class="rex-page-edit-content" placeholder="Write the answer here..."></textarea>
                            @error('answer')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </main>

            <aside class="rex-page-edit-sidebar" style="position:sticky;top:1rem;display:grid;gap:16px">
                <section class="rex-page-edit-card rex-page-edit-publish">
                    <div class="rex-page-edit-card-heading"><h2>Publish</h2></div>
                    <div class="space-y-3">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveDraft" class="rex-page-edit-button rex-page-edit-button--primary"><span wire:loading.remove wire:target="saveDraft">Save</span><span wire:loading wire:target="saveDraft">Saving...</span></button>
                        <button type="button" wire:click="saveAndExit" wire:loading.attr="disabled" wire:target="saveAndExit" class="rex-page-edit-button rex-page-edit-button--secondary"><span wire:loading.remove wire:target="saveAndExit">Save &amp; Exit</span><span wire:loading wire:target="saveAndExit">Saving...</span></button>
                        @can(CmsPermissions::resourcePermission($resource, 'publish'))<button type="button" wire:click="publish" wire:loading.attr="disabled" wire:target="publish" class="rex-page-edit-button rex-page-edit-button--secondary"><span wire:loading.remove wire:target="publish">Publish</span><span wire:loading wire:target="publish">Publishing...</span></button>@endcan
                    </div>
                    <a href="{{ route('admin.faqs.index') }}" wire:navigate class="rex-page-edit-cancel">Cancel</a>
                </section>
                <section class="rex-page-edit-card"><div class="rex-page-edit-card-heading"><h2>Status</h2></div><label for="faq-status" class="sr-only">Status</label><select id="faq-status" wire:model="status"><option value="draft">Draft</option><option value="published">Published</option></select></section>
                <section class="rex-page-edit-card"><div class="rex-page-edit-card-heading"><h2>Display order</h2></div><label for="faq-sort-order">Sort order</label><input id="faq-sort-order" wire:model="sort_order" type="number" min="0" /></section>
            </aside>
        </form>
    </div>
</div>
