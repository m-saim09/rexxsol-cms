<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public string $actor = '';

    public string $module = '';

    public string $action = '';

    public string $date = '';

    public int $perPage = 10;

    public bool $filtersOpen = false;

    #[Locked]
    public ?int $selectedId = null;

    public function updated($property): void
    {
        if (in_array($property, ['search', 'actor', 'module', 'action', 'date', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->actor = '';
        $this->module = '';
        $this->action = '';
        $this->date = '';
        $this->resetPage();
    }

    public function open(int $id): void
    {
        Gate::authorize('view-activity-logs');
        ActivityLog::findOrFail($id);
        $this->selectedId = $id;
        Flux::modal('activity-detail')->show();
    }

    public function close(): void
    {
        Gate::authorize('view-activity-logs');
        $this->selectedId = null;
        Flux::modal('activity-detail')->close();
    }

    public function clearSelected(): void
    {
        $this->selectedId = null;
    }

    public function render()
    {
        Gate::authorize('view-activity-logs');
        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;
        $query = ActivityLog::with('user')->when($this->search, fn ($q) => $q->where('subject_label', 'like', '%'.$this->search.'%'))
            ->when($this->actor, fn ($q) => $q->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$this->actor.'%')))
            ->when($this->module, fn ($q) => $q->where('module', $this->module))->when($this->action, fn ($q) => $q->where('action', $this->action))
            ->when(preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->date), fn ($q) => $q->whereDate('created_at', $this->date));

        $items = null;
        $selected = null;
        $modules = collect();
        $actions = collect();
        $loadError = false;

        try {
            $items = $query->latest('id')->paginate($perPage)->through(fn (ActivityLog $item) => [
                'id' => $item->id,
                'actor' => $item->user?->name ?? 'System / removed user',
                'action' => $item->action,
                'module' => $item->module,
                'record' => $item->subject_label,
                'created_at' => $item->created_at?->timezone(config('app.timezone')),
            ]);
            $modules = ActivityLog::query()->select('module')->distinct()->orderBy('module')->pluck('module');
            $actions = ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');

            if ($this->selectedId) {
                $item = ActivityLog::with('user')->find($this->selectedId);
                if ($item) {
                    $selected = [
                        'actor' => $item->user?->name ?? 'System / removed user',
                        'action' => $item->action,
                        'module' => $item->module,
                        'record' => $item->subject_label,
                        'created_at' => $item->created_at?->timezone(config('app.timezone')),
                        'changes' => collect($item->safeChanges())->map(fn ($change) => [
                            'old' => is_scalar($change['old'] ?? null) ? (string) $change['old'] : '—',
                            'new' => is_scalar($change['new'] ?? null) ? (string) $change['new'] : '—',
                        ])->all(),
                    ];
                }
            }
        } catch (QueryException $exception) {
            report($exception);
            $items = null;
            $selected = null;
            $loadError = true;
        }

        return view('livewire.admin.activity-logs', [
            'items' => $items,
            'selected' => $selected,
            'loadError' => $loadError,
            'filterCount' => collect([$this->actor, $this->module, $this->action, $this->date])->filter()->count(),
            'hasFilters' => $this->search !== '' || $this->actor !== '' || $this->module !== '' || $this->action !== '' || $this->date !== '',
            'modules' => $modules,
            'actions' => $actions,
        ])->layout('layouts.admin')->title('Activity Logs');
    }
}
