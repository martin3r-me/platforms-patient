<?php

namespace Platform\Patient\Support;

use Illuminate\Support\Carbon;
use Platform\Patient\Models\Lookup;

/**
 * Lookups — echte DB-Lookups als EINZIGE Quelle (per Settings/MCP voll editierbar).
 *
 * Defaults leben nur als Seeds in config('patient.lookup_seeds') und werden EINMALIG
 * pro Team in die `patient_lookups`-Tabelle geschrieben (lazy, idempotent). Zur Laufzeit
 * wird ausschließlich die DB gelesen. GdB/MdE ist eine feste Zahlen-Skala (kein Lookup).
 */
class Lookups
{
    /** Lookup-Typen (alle DB-basiert, seedbar, editierbar). */
    public const TYPES = ['gender', 'marital_status', 'nationality', 'language', 'country', 'health_insurance'];

    /**
     * Seedet fehlende Typen für ein Team aus den Config-Seeds (idempotent).
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
            foreach ((array) config("patient.lookup_seeds.$type", []) as $value) {
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
