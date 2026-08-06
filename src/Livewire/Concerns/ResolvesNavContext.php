<?php

namespace Platform\Patient\Livewire\Concerns;

use Platform\Patient\Services\PatientNavigationRegistry;

/**
 * Löst den Navigations-Kontext aus der URL (?lens=&node=) auf: die Patientenliste
 * des gewählten Knotens (für die innere Seiten-Sidebar). Nur Navigation.
 */
trait ResolvesNavContext
{
    /**
     * @return array{lensKey:?string, nodeId:?string, nodeLabel:?string, patients:array}
     */
    protected function navContext(int $teamId): array
    {
        $lensKey = request()->query('lens');
        $nodeId  = request()->query('node');

        if ($lensKey === 'search') {
            $lensKey = null;
        }

        $patients  = [];
        $nodeLabel = null;

        if ($lensKey !== null && $nodeId !== null) {
            $lens = resolve(PatientNavigationRegistry::class)->lens($lensKey);
            if ($lens) {
                $patients = $lens->patientsFor((string) $nodeId, $teamId);
                foreach ($lens->tree($teamId) as $n) {
                    if ((string) $n['id'] === (string) $nodeId) {
                        $nodeLabel = $n['label'];
                        break;
                    }
                }
            }
        }

        return [
            'lensKey'   => $lensKey,
            'nodeId'    => $nodeId,
            'nodeLabel' => $nodeLabel,
            'patients'  => $patients,
        ];
    }
}
