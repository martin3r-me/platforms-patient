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
     * Lookup-SEEDS — werden EINMALIG pro Team in die `patient_lookups`-Tabelle geseedet
     * (lazy, idempotent). Zur Laufzeit ist die DB die einzige Quelle; diese Listen werden
     * NUR beim ersten Zugriff eines Teams verwendet. Danach voll editierbar per Settings/MCP.
     */
    'lookup_seeds' => [
        'gender'         => ['weiblich', 'männlich', 'divers', 'unbekannt'],
        'marital_status' => ['ledig', 'verheiratet', 'geschieden', 'verwitwet', 'eingetragene Lebenspartnerschaft', 'getrennt lebend'],
        'nationality'    => ['deutsch', 'türkisch', 'polnisch', 'italienisch', 'russisch', 'syrisch', 'rumänisch', 'österreichisch', 'sonstige'],
        'language'       => ['Deutsch', 'Englisch', 'Türkisch', 'Russisch', 'Polnisch', 'Arabisch'],
        'country'        => ['Deutschland', 'Österreich', 'Schweiz', 'Polen', 'Türkei', 'Italien', 'Frankreich'],
        'health_insurance' => ['AOK', 'Barmer', 'Techniker Krankenkasse (TK)', 'DAK-Gesundheit', 'IKK classic', 'KKH', 'Knappschaft', 'hkk', 'privat'],
    ],
];
