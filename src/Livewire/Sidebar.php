<?php

namespace Platform\Patient\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Patient\Models\Patient as PatientModel;
use Platform\Patient\Services\PatientNavigationRegistry;

/**
 * Patient-Haupt-Sidebar — Navigations-Linsen-fähig. Ist eine Linse registriert
 * (z. B. „Betrieb" aus der Betriebsmedizin), zeigt die Sidebar deren Baum als
 * führende Dimension (Betrieb-first) + Umschalter zu „Suche". Ohne Linse rein
 * Patient-first (Patienten + Zuletzt). Dashboard/Einstellungen immer erreichbar.
 */
class Sidebar extends Component
{
    public function render()
    {
        $user = Auth::user();
        $team = $user?->currentTeam?->id;

        $registry = resolve(PatientNavigationRegistry::class);
        $lenses   = $registry->lenses();

        // Aktive Linse: ?lens= — 'search' = explizit Patient-first; sonst Default = erste Linse.
        $requested     = request()->query('lens');
        $activeLensKey = $requested;
        if ($requested === null && !empty($lenses)) {
            $activeLensKey = $lenses[0]->key();
        }
        if ($requested === 'search') {
            $activeLensKey = null;
        }

        $activeLens = $registry->lens($activeLensKey);
        $activeNode = request()->query('node');

        $tree = ($activeLens && $team) ? $activeLens->tree($team) : [];

        // Patient-first: zuletzt besuchte Patienten (nur ohne aktive Linse).
        $patients = collect();
        if (!$activeLens && $team) {
            $patients = PatientModel::query()
                ->forTeam($team)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit(15)
                ->get();
        }

        return view('patient::livewire.sidebar', [
            'lenses'        => $lenses,
            'activeLens'    => $activeLens,
            'activeLensKey' => $activeLensKey,
            'activeNode'    => $activeNode,
            'tree'          => $tree,
            'patients'      => $patients,
        ]);
    }
}
