<?php

namespace Platform\Patient\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Patient\Models\Lookup as LookupModel;
use Platform\Patient\Support\Lookups;

class Index extends Component
{
    public string $activeType = 'nationality';
    public string $newValue = '';

    public array $typeLabels = [
        'gender'           => 'Geschlecht',
        'marital_status'   => 'Familienstand',
        'nationality'      => 'Nationalität',
        'language'         => 'Sprache',
        'country'          => 'Land',
        'health_insurance' => 'Krankenkasse',
    ];

    public function mount(): void
    {
        Lookups::ensureSeeded($this->teamId());
    }

    public function selectType(string $type): void
    {
        if (in_array($type, Lookups::TYPES, true)) {
            $this->activeType = $type;
            $this->newValue = '';
            $this->resetValidation();
        }
    }

    protected function teamId(): int
    {
        return (int) Auth::user()->currentTeam->id;
    }

    public function add(): void
    {
        $this->validate(['newValue' => ['required', 'string', 'max:255']]);

        $value = trim($this->newValue);
        if ($value === '') {
            return;
        }

        // Nicht doppelt zu Defaults oder bestehenden Team-Werten anlegen.
        $existing = array_map('mb_strtolower', Lookups::optionsFor($this->activeType, $this->teamId()));
        if (in_array(mb_strtolower($value), $existing, true)) {
            $this->addError('newValue', 'Wert existiert bereits.');
            return;
        }

        LookupModel::create([
            'type'   => $this->activeType,
            'value'  => $value,
            'active' => true,
        ]);

        $this->newValue = '';
        $this->dispatch('toast', message: 'Wert hinzugefügt.', type: 'success');
    }

    public function delete(int $id): void
    {
        LookupModel::query()->forTeam($this->teamId())->findOrFail($id)->delete();
    }

    public function render()
    {
        $team = $this->teamId();

        $teamValues = LookupModel::query()
            ->forTeam($team)
            ->ofType($this->activeType)
            ->orderBy('position')
            ->orderBy('value')
            ->get();

        return view('patient::livewire.settings.index', [
            'types'      => Lookups::TYPES,
            'teamValues' => $teamValues,
        ])->layout('platform::layouts.app');
    }
}
