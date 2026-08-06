<?php

namespace Platform\Patient\Livewire\Patient;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Patient\Models\Patient as PatientModel;
use Platform\Patient\Support\Lookups;

class Show extends Component
{
    #[Locked]
    public int $patientId;

    public array $form = [];

    /** Bearbeitbare Felder (Team/Besitz wird bei jeder Aktion re-validiert). */
    protected array $fields = [
        'first_name', 'last_name', 'title', 'birth_name', 'birth_date', 'birth_place',
        'gender', 'nationality', 'marital_status', 'language', 'country', 'deceased_at',
        'health_insurance', 'social_security_number', 'lab_number', 'lab_number_external',
        'family_doctor', 'disability_degree', 'reduced_earning_capacity', 'equal_status',
        'phone', 'phone_private', 'mobile', 'fax', 'email_work', 'email_private',
        'street', 'postal_code', 'city', 'notes',
    ];

    public function mount(int $patient): void
    {
        $model = $this->resolvePatient($patient);
        $this->patientId = $model->id;

        foreach ($this->fields as $f) {
            $value = $model->{$f};
            if (in_array($f, ['birth_date', 'deceased_at'], true)) {
                $value = optional($value)->format('Y-m-d');
            }
            $this->form[$f] = $value;
        }
    }

    protected function resolvePatient(int $id): PatientModel
    {
        $team = Auth::user()->currentTeam;

        return PatientModel::query()
            ->forTeam($team->id)
            ->findOrFail($id);
    }

    protected function rules(): array
    {
        return [
            'form.first_name'               => ['nullable', 'string', 'max:255'],
            'form.last_name'                => ['nullable', 'string', 'max:255'],
            'form.birth_date'               => ['nullable', 'date'],
            'form.deceased_at'              => ['nullable', 'date'],
            'form.email_work'               => ['nullable', 'email', 'max:255'],
            'form.email_private'            => ['nullable', 'email', 'max:255'],
            'form.disability_degree'        => ['nullable', 'integer', 'min:0', 'max:100'],
            'form.reduced_earning_capacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'form.equal_status'             => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $model = $this->resolvePatient($this->patientId);

        $data = [];
        foreach ($this->fields as $f) {
            $data[$f] = $this->form[$f] === '' ? null : $this->form[$f];
        }
        $data['equal_status'] = (bool) ($this->form['equal_status'] ?? false);

        $model->update($data);

        $this->dispatch('toast', message: 'Patient gespeichert.', type: 'success');
    }

    public function delete()
    {
        $model = $this->resolvePatient($this->patientId);
        $model->delete();

        return $this->redirectRoute('patient.patients.index', navigate: true);
    }

    public function render()
    {
        $model = $this->resolvePatient($this->patientId);
        $team = (int) Auth::user()->currentTeam->id;

        // Bestehenden Wert immer als Option behalten (auch wenn nicht in der Liste).
        $ensure = function (array $opts, $current): array {
            $current = (string) $current;
            $flat = array_map('strval', $opts);
            if ($current !== '' && !in_array($current, $flat, true)) {
                array_unshift($opts, $current);
            }
            return $opts;
        };

        $lookups = [
            'gender'           => $ensure(Lookups::optionsFor('gender', $team), $this->form['gender'] ?? null),
            'marital_status'   => $ensure(Lookups::optionsFor('marital_status', $team), $this->form['marital_status'] ?? null),
            'nationality'      => $ensure(Lookups::optionsFor('nationality', $team), $this->form['nationality'] ?? null),
            'language'         => $ensure(Lookups::optionsFor('language', $team), $this->form['language'] ?? null),
            'country'          => $ensure(Lookups::optionsFor('country', $team), $this->form['country'] ?? null),
            'health_insurance' => $ensure(Lookups::optionsFor('health_insurance', $team), $this->form['health_insurance'] ?? null),
        ];

        $panels = resolve(\Platform\Patient\Services\PatientPanelRegistry::class)
            ->panelsFor((int) $model->id, $team);

        return view('patient::livewire.patient.show', [
            'patient'  => $model,
            'lookups'  => $lookups,
            'gdbSteps' => Lookups::disabilitySteps(),
            'panels'   => $panels,
        ])->layout('platform::layouts.app');
    }
}
