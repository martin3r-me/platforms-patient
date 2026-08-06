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

    /**
     * Feste Wertebereiche — standardisiert, NICHT team-editierbar (fester Select).
     */
    'value_sets' => [
        'gender'            => ['weiblich', 'männlich', 'divers', 'unbekannt'],
        'disability_degree' => [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100], // GdB/MdE in 10er-Schritten
    ],

    /**
     * Lookup-Defaults — Basislisten, immer verfügbar. Teams erweitern per
     * patient_lookups (MCP/UI); die Optionen sind Defaults ∪ Team-Werte.
     */
    'lookup_defaults' => [
        'marital_status' => ['ledig', 'verheiratet', 'geschieden', 'verwitwet', 'eingetragene Lebenspartnerschaft', 'getrennt lebend'],
        'nationality'    => ['deutsch', 'türkisch', 'polnisch', 'italienisch', 'russisch', 'syrisch', 'rumänisch', 'österreichisch', 'sonstige'],
        'language'       => ['Deutsch', 'Englisch', 'Türkisch', 'Russisch', 'Polnisch', 'Arabisch'],
        'country'        => ['Deutschland', 'Österreich', 'Schweiz', 'Polen', 'Türkei', 'Italien', 'Frankreich'],
        'health_insurance' => ['AOK', 'Barmer', 'Techniker Krankenkasse (TK)', 'DAK-Gesundheit', 'IKK classic', 'KKH', 'Knappschaft', 'hkk', 'privat'],
    ],
];
