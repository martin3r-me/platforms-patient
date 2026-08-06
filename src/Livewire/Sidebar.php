<?php

namespace Platform\Patient\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Patient\Models\Patient as PatientModel;

class Sidebar extends Component
{
    public function render()
    {
        $user = Auth::user();
        $patients = collect();

        if ($user && $user->currentTeam) {
            $patients = PatientModel::query()
                ->forTeam($user->currentTeam->id)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit(15)
                ->get();
        }

        return view('patient::livewire.sidebar', [
            'patients' => $patients,
        ]);
    }
}
