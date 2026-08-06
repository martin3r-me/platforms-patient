<?php

namespace Platform\Patient\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Patient\Models\Patient as PatientModel;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $team = $user?->currentTeam;

        $stats = [
            'total' => $team
                ? PatientModel::query()->forTeam($team->id)->count()
                : 0,
        ];

        return view('patient::livewire.dashboard', [
            'stats'       => $stats,
            'currentDate' => now()->format('d.m.Y'),
        ])->layout('platform::layouts.app');
    }
}
