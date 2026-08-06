<?php

namespace Platform\Patient\Contracts;

/**
 * PatientPanelProvider — ein Fachmodul steuert ein Panel zur Patienten-Akte bei
 * (Termine, Beschäftigung, Labor …). patient kennt die Fachmodule NICHT; sie docken
 * additiv an (wie Org-EntityLinkProvider). Alles loose: nur patient_id + fertige URLs.
 */
interface PatientPanelProvider
{
    /**
     * Panel für einen Patienten — oder null, wenn dieses Modul nichts beizusteuern hat.
     *
     * Rückgabe:
     *  [
     *    'key'    => 'appointments',
     *    'title'  => 'Termine',
     *    'icon'   => 'calendar-days',
     *    'order'  => 20,                         // Sortierung in der Akte
     *    'items'  => [ ['title'=>…, 'subtitle'=>…, 'meta'=>…, 'url'=>…], … ],
     *    'actions'=> [ ['label'=>'Neuer Termin', 'url'=>…] ],   // optional
     *    'empty'  => 'Noch keine Termine',        // optional
     *  ]
     */
    public function panel(int $patientId, int $teamId): ?array;
}
