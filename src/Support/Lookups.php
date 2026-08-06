<?php

namespace Platform\Patient\Support;

use Illuminate\Support\Carbon;
use Platform\Patient\Models\Lookup;

/**
 * Lookups — echte DB-Lookups als EINZIGE Quelle (per Settings/MCP voll editierbar).
 *
 * Seed-Defaults liegen als KONSTANTE im Code (NICHT in config) — bewusst, damit sie
 * unabhängig vom Config-Cache der Instanz zuverlässig verfügbar sind. Sie werden EINMALIG
 * pro Team in die `patient_lookups`-Tabelle geschrieben (lazy, idempotent). Zur Laufzeit
 * wird ausschließlich die DB gelesen. GdB/MdE ist eine feste Zahlen-Skala (kein Lookup).
 */
class Lookups
{
    /** Lookup-Typen (alle DB-basiert, seedbar, editierbar). */
    public const TYPES = ['gender', 'marital_status', 'nationality', 'language', 'country', 'health_insurance', 'address_type', 'phone_type', 'email_type'];

    /** Seed-Defaults je Typ (nur beim ersten Zugriff eines Teams verwendet). */
    public const SEEDS = [
        'gender'           => ['weiblich', 'männlich', 'divers', 'unbekannt'],
        'marital_status'   => ['ledig', 'verheiratet', 'geschieden', 'verwitwet', 'eingetragene Lebenspartnerschaft', 'getrennt lebend'],
        'nationality'      => ['deutsch', 'türkisch', 'polnisch', 'italienisch', 'russisch', 'syrisch', 'rumänisch', 'österreichisch', 'sonstige'],
        'language'         => ['Deutsch', 'Englisch', 'Türkisch', 'Russisch', 'Polnisch', 'Arabisch'],
        'country'          => ['Deutschland', 'Österreich', 'Schweiz', 'Polen', 'Türkei', 'Italien', 'Frankreich'],
        'health_insurance' => ['AOK', 'Barmer', 'Techniker Krankenkasse (TK)', 'DAK-Gesundheit', 'IKK classic', 'KKH', 'Knappschaft', 'hkk', 'privat'],
        'address_type'     => ['Hauptwohnsitz', 'Nebenwohnsitz', 'Arbeit', 'Postadresse'],
        'phone_type'       => ['Mobil', 'Festnetz', 'Geschäftlich', 'Fax'],
        'email_type'       => ['Privat', 'Geschäftlich'],
    ];

    /**
     * Seedet fehlende Typen für ein Team aus den Code-Seeds (idempotent).
     */
    public static function ensureSeeded(int $teamId): void
    {
        if ($teamId <= 0) {
            return;
        }

        $existing = Lookup::query()->forTeam($teamId)->distinct()->pluck('type')->all();
        $now = Carbon::now();
        $rows = [];

        foreach (self::TYPES as $type) {
            if (in_array($type, $existing, true)) {
                continue;
            }
            $pos = 0;
            foreach (self::SEEDS[$type] ?? [] as $value) {
                $rows[] = [
                    'team_id' => $teamId, 'type' => $type, 'value' => $value, 'label' => null,
                    'position' => $pos++, 'active' => true, 'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }

        if ($rows) {
            Lookup::query()->insert($rows);
        }
    }

    /**
     * Aktive Optionen eines Typs (DB-only; seedet bei Bedarf).
     *
     * @return array<int,string>
     */
    public static function optionsFor(string $type, ?int $teamId): array
    {
        if (!$teamId) {
            return [];
        }

        self::ensureSeeded((int) $teamId);

        return Lookup::query()
            ->forTeam((int) $teamId)
            ->ofType($type)
            ->where('active', true)
            ->orderBy('position')
            ->orderBy('value')
            ->pluck('value')
            ->all();
    }

    /**
     * Feste GdB/MdE-Skala (0–100 in 10er-Schritten) — kein Lookup, standardisiert.
     *
     * @return array<int,int>
     */
    public static function disabilitySteps(): array
    {
        return range(0, 100, 10);
    }
}
