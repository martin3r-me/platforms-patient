<?php

/**
 * Patient — fachneutrale Patienten-Stammdaten.
 *
 * Wurzel-Modul der medizinischen Modul-Plattform: isolierte, verschlüsselte
 * Personen-Stammdaten (Schweigepflicht). Kennt KEINE Fachlogik und KEINEN
 * Arbeitgeber-Bezug — Fachmodule (health, …) und spätere Schienen (Labor,
 * Messgeräte) hängen an diesem Modul, nie umgekehrt.
 *
 * Konvention: englische Identifier, deutsche Anzeige-Labels.
 */

return [
    'routing' => [
        'mode'   => env('PATIENT_MODE', 'path'),
        'prefix' => 'patient',
    ],

    'guard' => 'web',

    'navigation' => [
        'route' => 'patient.dashboard',
        'icon'  => 'heroicon-o-identification',
        'order' => 34,
    ],

    'sidebar' => [
        [
            'group' => 'Patient',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'patient.dashboard',
                    'icon'  => 'heroicon-o-home',
                ],
                [
                    'label' => 'Patienten',
                    'route' => 'patient.patients.index',
                    'icon'  => 'heroicon-o-identification',
                ],
                [
                    'label' => 'Einstellungen',
                    'route' => 'patient.settings',
                    'icon'  => 'heroicon-o-cog-6-tooth',
                ],
            ],
        ],
    ],
    // Lookup-Seed-Defaults liegen bewusst im Code (Support\Lookups::SEEDS), NICHT hier —
    // damit sie unabhängig vom Config-Cache der Instanz verfügbar sind.
];
