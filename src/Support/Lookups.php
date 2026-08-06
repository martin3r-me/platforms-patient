<?php

namespace Platform\Patient\Support;

use Platform\Patient\Models\Lookup;

/**
 * Lookups — liefert Select-Optionen: Config-Defaults ∪ Team-Lookups.
 *
 * - value_sets: feste, standardisierte Wertebereiche (Geschlecht, GdB-Schritte).
 * - lookup types: Defaults aus config + team-eigene Werte (patient_lookups).
 */
class Lookups
{
    /** Lookup-Typen mit Config-Defaults + Team-Erweiterung. */
    public const TYPES = ['marital_status', 'nationality', 'language', 'country', 'health_insurance'];

    /**
     * Merged Optionen für einen Lookup-Typ: Defaults zuerst, dann aktive Team-Werte, unique.
     *
     * @return array<int,string>
     */
    public static function optionsFor(string $type, ?int $teamId): array
    {
        $defaults = (array) config("patient.lookup_defaults.$type", []);

        $teamValues = [];
        if ($teamId) {
            $teamValues = Lookup::query()
                ->forTeam($teamId)
                ->ofType($type)
                ->where('active', true)
                ->orderBy('position')
                ->orderBy('value')
                ->pluck('value')
                ->all();
        }

        return array_values(array_unique(array_merge($defaults, $teamValues)));
    }

    /**
     * Fester Wertebereich (nicht team-editierbar).
     *
     * @return array<int,mixed>
     */
    public static function valueSet(string $key): array
    {
        return (array) config("patient.value_sets.$key", []);
    }
}
