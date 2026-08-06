{{--
    Patient · Dashboard — nx-Design-System.
    Shell bleibt x-ui-page*, Inhalt ausschließlich x-nx-* + var(--nx-*).
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Patienten" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Patienten', 'icon' => 'identification'],
        ]">
            <x-nx-button variant="primary" size="sm" :href="route('patient.patients.index')" wire:navigate>
                @svg('heroicon-o-identification', 'w-4 h-4')
                <span>Zur Patientenliste</span>
            </x-nx-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        <x-nx-stat-grid :cols="1">
            <a href="{{ route('patient.patients.index') }}" wire:navigate>
                <x-nx-stat label="Patienten" :value="$stats['total']" icon="heroicon-o-identification" hint="im Team" />
            </a>
        </x-nx-stat-grid>

        @if($stats['total'] === 0)
            <x-nx-card>
                <x-nx-empty icon="heroicon-o-identification">
                    Noch keine Patienten. Lege den ersten in der Patientenliste an.
                    <x-slot name="action">
                        <x-nx-button variant="secondary" size="sm" :href="route('patient.patients.index')" wire:navigate>
                            Zur Patientenliste
                        </x-nx-button>
                    </x-slot>
                </x-nx-empty>
            </x-nx-card>
        @endif
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-3">Patienten</h3>
                    <div class="text-sm text-[color:var(--nx-muted)]">Noch keine Einträge.</div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-3">Letzte Aktivitäten</h3>
                    <div class="text-sm text-[color:var(--nx-muted)]">Keine Aktivitäten verfügbar.</div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
