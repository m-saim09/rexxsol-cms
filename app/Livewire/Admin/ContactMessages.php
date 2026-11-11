<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ContactMessages extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $date = '';

    public bool $filtersOpen = false;

    #[Locked]
    public ?int $selectedId = null;

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'date'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'date']);
        $this->resetPage();
    }

    public function close(): void
    {
        $this->selectedId = null;
    }

    public function open(int $id): void
    {
        Gate::authorize('contact_messages.view');
        $message = ContactMessage::findOrFail($id);
        $this->selectedId = $id;
        if ($message->status === 'new' && Gate::allows('contact_messages.update')) {
            $this->changeStatus('read');
        }
    }

    public function changeStatus(string $status): void
    {
        Gate::authorize('contact_messages.update');
        validator(['status' => $status], ['status' => ['required', Rule::in(ContactMessage::STATUSES)]])->validate();
        DB::transaction(function () use ($status) {
            $message = ContactMessage::lockForUpdate()->findOrFail($this->selectedId);
            $message->status = $status;
            $message->read_at = $status === 'new' ? null : ($message->read_at ?? now());
            $message->replied_at = $status === 'replied' ? ($message->replied_at ?? now()) : ($status === 'new' ? null : $message->replied_at);
            $message->save();
        });
        Flux::toast(variant: 'success', text: 'Message marked as '.$status.'.');
        $this->dispatch('contact-status-changed');
    }

    public function render()
    {
        Gate::authorize('contact_messages.view');

        $messageQuery = ContactMessage::query();

        if ($this->search !== '') {
            $search = $this->search;
            $messageQuery->where(fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('subject', 'like', '%'.$search.'%'));
        }

        if (in_array($this->status, ContactMessage::STATUSES, true)) {
            $messageQuery->where('status', $this->status);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->date) === 1) {
            $messageQuery->whereDate('created_at', $this->date);
        }

        return view('livewire.admin.contact-messages', [
            'items' => $messageQuery->orderByDesc('created_at')->orderByDesc('id')->paginate(10),
            'selected' => $this->selectedId ? ContactMessage::find($this->selectedId) : null,
        ])->layout('layouts.admin')->title('Contact Messages');
    }
}
