<?php

namespace Platform\Patient\Livewire\Patient;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Patient\Models\Patient as PatientModel;

class Index extends Component
{
    public string $search = '';

    public bool $showCreate = false;
    public string $first_name = '';
    public string $last_name = '';
    public ?string $birth_date = null;
    public ?string $duplicateWarning = null;

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
        ];
    }

    public function updatedShowCreate(): void
    {
        $this->reset(['first_name', 'last_name', 'birth_date', 'duplicateWarning']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->validate();

        $team = Auth::user()->currentTeam;

        $duplicate = PatientModel::query()
            ->forTeam($team->id)
            ->matching($this->first_name, $this->last_name, $this->birth_date ?: null)
            ->first();

        if ($duplicate) {
            $this->duplicateWarning = 'Ein Patient mit gleichem Namen und Geburtsdatum existiert bereits.';
            return;
        }

        $patient = PatientModel::create([
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'birth_date' => $this->birth_date ?: null,
        ]);

        return $this->redirectRoute('patient.patients.show', ['patient' => $patient->id], navigate: true);
    }

    public function render()
    {
        $team = Auth::user()->currentTeam;

        $patients = PatientModel::query()
            ->forTeam($team->id)
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('last_name', 'like', $term)
                      ->orWhere('first_name', 'like', $term)
                      ->orWhere('lab_number', 'like', $term);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('patient::livewire.patient.index', [
            'patients' => $patients,
        ])->layout('platform::layouts.app');
    }
}
