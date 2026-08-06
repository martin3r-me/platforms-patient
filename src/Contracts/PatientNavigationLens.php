<?php

namespace Platform\Patient\Contracts;

/**
 * PatientNavigationLens — eine steckbare Navigations-Perspektive für das (fachneutrale)
 * patient-Modul. Ein Fachmodul/eine Praxis-Dimension liefert einen Baum (führende
 * Dimension) und die Patienten je Knoten. So bleibt patient generisch: Betriebsmedizin
 * bringt die „Betrieb"-Linse mit, eine andere Praxis „Überweiser"/„Familie"/„Kasse".
 *
 * patient kennt die Fachmodule NICHT; Linsen docken additiv an (Registry-Inversion,
 * wie PatientPanelProvider / CompanyPatientProvider).
 */
interface PatientNavigationLens
{
    /** Stabiler Schlüssel, z. B. 'betrieb'. Landet im ?lens=-Query. */
    public function key(): string;

    /** Anzeige-Label im Umschalter, z. B. 'Betrieb'. */
    public function label(): string;

    /** Heroicon-Name fürs Label. */
    public function icon(): string;

    /** Sortierung im Umschalter (kleiner = weiter vorn). */
    public function order(): int;

    /**
     * Der Baum der führenden Dimension (Wurzeln + Nachkommen, depth-annotiert).
     *
     * @return array<int,array{id:(int|string),label:string,depth:int,meta:?string}>
     */
    public function tree(int $teamId): array;

    /**
     * Patienten zu einem Baum-Knoten (inkl. dessen Teilbaum). Nur Navigation:
     * Name + fertige URL zur Akte — keine Patientendaten im Fachmodul.
     *
     * @return array<int,array{patient_id:int,name:string,subtitle:?string,meta:?string,url:string}>
     */
    public function patientsFor(string $nodeId, int $teamId): array;
}
