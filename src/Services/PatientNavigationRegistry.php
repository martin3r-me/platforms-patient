<?php

namespace Platform\Patient\Services;

use Platform\Patient\Contracts\PatientNavigationLens;

/**
 * PatientNavigationRegistry — sammelt die von Fachmodulen registrierten Navigations-Linsen.
 * Singleton; Fachmodule rufen ->register(...) in ihrem boot(). Ist keine Linse registriert,
 * bleibt das patient-Modul rein Patient-first (Suche/Liste) — fachneutraler Default.
 */
class PatientNavigationRegistry
{
    /** @var array<int,PatientNavigationLens> */
    protected array $lenses = [];

    public function register(PatientNavigationLens $lens): void
    {
        $this->lenses[] = $lens;
    }

    /** @return array<int,PatientNavigationLens> nach order() sortiert */
    public function lenses(): array
    {
        $lenses = $this->lenses;
        usort($lenses, fn ($a, $b) => $a->order() <=> $b->order());

        return $lenses;
    }

    public function has(): bool
    {
        return !empty($this->lenses);
    }

    public function lens(?string $key): ?PatientNavigationLens
    {
        if (!$key) {
            return null;
        }
        foreach ($this->lenses as $lens) {
            if ($lens->key() === $key) {
                return $lens;
            }
        }
        return null;
    }

    /** Standard-Linse (erste nach order) — für „Betrieb-first"-Default in der Betriebsmedizin. */
    public function default(): ?PatientNavigationLens
    {
        return $this->lenses()[0] ?? null;
    }
}
