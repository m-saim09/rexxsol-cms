@if ($selectedKey === 'about')
    <div>
        <h3 class="font-semibold">About page details</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="cms-label">Experience years<input wire:model="form.experience_years" maxlength="12" class="cms-input mt-2" placeholder="15" /></label>
            <label class="cms-label">Story heading<input wire:model="form.story_heading" maxlength="255" class="cms-input mt-2" placeholder="Experience that turns ideas into working solutions." /></label>
            <label class="cms-label sm:col-span-2">Story closing statement<input wire:model="form.story_close" maxlength="255" class="cms-input mt-2" placeholder="Built on understanding. Focused on delivery." /></label>
            <label class="cms-label">Mission title<input wire:model="form.mission_title" maxlength="100" class="cms-input mt-2" placeholder="Our mission" /></label>
            <label class="cms-label">Vision title<input wire:model="form.vision_title" maxlength="100" class="cms-input mt-2" placeholder="Our vision" /></label>
            <label class="cms-label">Mission description<textarea wire:model="form.mission_description" maxlength="500" rows="3" class="cms-input mt-2" placeholder="Make technology useful, accessible and aligned with the way businesses work."></textarea></label>
            <label class="cms-label">Vision description<textarea wire:model="form.vision_description" maxlength="500" rows="3" class="cms-input mt-2" placeholder="Help businesses move forward with dependable digital solutions."></textarea></label>
            <label class="cms-label">Thinking section eyebrow<input wire:model="form.thinking_eyebrow" maxlength="100" class="cms-input mt-2" placeholder="How we think" /></label>
            <label class="cms-label">Thinking section heading<input wire:model="form.thinking_heading" maxlength="255" class="cms-input mt-2" placeholder="Clear thinking. Practical delivery." /></label>
        </div>
    </div>
    <div>
        <div class="flex items-center justify-between"><h3 class="font-semibold">About points</h3><button type="button" wire:click="addItem" class="cms-secondary">Add point</button></div>
        <div class="mt-4 space-y-3">
            @foreach (($form['items'] ?? []) as $index => $item)
                <div class="grid gap-3 rounded border border-[#e5eaf2] p-3 sm:grid-cols-[1fr_1fr_auto]">
                    <input wire:model="form.items.{{ $index }}.title" class="cms-input" placeholder="Title" />
                    <input wire:model="form.items.{{ $index }}.description" class="cms-input" placeholder="Short description" />
                    <button type="button" wire:click="removeItem({{ $index }})" aria-label="Remove point" class="cms-link">Remove</button>
                </div>
            @endforeach
        </div>
    </div>
@elseif ($selectedKey === 'process')
    <div>
        <h3 class="font-semibold">Process steps</h3>
        <div class="mt-4 space-y-3">
            @foreach (($form['steps'] ?? []) as $index => $step)
                <div class="grid gap-3 rounded border border-[#e5eaf2] p-3 sm:grid-cols-2">
                    <label class="cms-label">Step {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }} title<input wire:model="form.steps.{{ $index }}.title" class="cms-input mt-2" /></label>
                    <label class="cms-label">Description<input wire:model="form.steps.{{ $index }}.description" class="cms-input mt-2" /></label>
                </div>
            @endforeach
        </div>
    </div>
@endif