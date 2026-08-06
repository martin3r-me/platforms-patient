<?php

namespace Platform\Patient\Services;

use Platform\Patient\Contracts\PatientPanelProvider;

/**
 * PatientPanelRegistry — sammelt die von Fachmodulen registrierten Akte-Panels.
 * Singleton; Fachmodule rufen ->register(...) in ihrem boot().
 */
class PatientPanelRegistry
{
    /** @var array<int,PatientPanelProvider> */
    protected array $providers = [];

    public function register(PatientPanelProvider $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Alle Panels für einen Patienten, sortiert nach 'order'. Fehlertolerant je Provider.
     *
     * @return array<int,array>
     */
    public function panelsFor(int $patientId, int $teamId): array
    {
        $panels = [];

        foreach ($this->providers as $provider) {
            try {
                $panel = $provider->panel($patientId, $teamId);
                if ($panel) {
                    $panels[] = $panel;
                }
            } catch (\Throwable $e) {
                // Ein defektes/abwesendes Panel darf die Akte nicht brechen.
            }
        }

        usort($panels, fn ($a, $b) => ($a['order'] ?? 100) <=> ($b['order'] ?? 100));

        return $panels;
    }
}
