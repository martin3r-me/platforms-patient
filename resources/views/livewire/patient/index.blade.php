{{--
    Patient · Patienten-Liste — nx-Design-System.
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Patienten" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Patienten', 'route' => 'patient.dashboard', 'icon' => 'identification'],
            ['label' => 'Liste'],
        ]">
            <x-nx-button variant="primary" size="sm" wire:click="$set('showCreate', true)">
                @svg('heroicon-o-plus', 'w-4 h-4')
                <span>Neuer Patient</span>
            </x-nx-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        <x-nx-input-text name="search" wire:model.live.debounce.300ms="search"
                         placeholder="Suche nach Name oder Labor-Nr …" />

        @if($patients->isEmpty())
            <x-nx-card>
                <x-nx-empty icon="heroicon-o-identification">
                    Noch keine Patienten. Lege den ersten über „Neuer Patient" an.
                </x-nx-empty>
            </x-nx-card>
        @else
            <x-nx-card flush>
                <x-nx-table>
                    <x-nx-table-header>
                        <x-nx-table-header-cell>Name</x-nx-table-header-cell>
                        <x-nx-table-header-cell>Geburtsdatum</x-nx-table-header-cell>
                        <x-nx-table-header-cell>Labor-Nr</x-nx-table-header-cell>
                    </x-nx-table-header>
                    <x-nx-table-body>
                        @foreach($patients as $patient)
                            <x-nx-table-row wire:key="patient-{{ $patient->id }}"
                                            :href="route('patient.patients.show', $patient->id)">
                                <x-nx-table-cell>{{ $patient->getDisplayName() }}</x-nx-table-cell>
                                <x-nx-table-cell>{{ optional($patient->birth_date)->format('d.m.Y') ?? '—' }}</x-nx-table-cell>
                                <x-nx-table-cell>{{ $patient->lab_number ?? '—' }}</x-nx-table-cell>
                            </x-nx-table-row>
                        @endforeach
                    </x-nx-table-body>
                </x-nx-table>
            </x-nx-card>
        @endif
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-3">Patienten</h3>
                    <div class="text-sm text-[color:var(--nx-muted)]">{{ $patients->count() }} Einträge.</div>
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

    {{-- Anlegen-Modal --}}
    <x-nx-modal wire:model="showCreate" size="md">
        <x-slot name="header">Neuer Patient</x-slot>
        <div class="space-y-4">
            @if($duplicateWarning)
                <x-nx-callout variant="warning" icon="heroicon-o-exclamation-triangle" title="Mögliche Dublette">
                    {{ $duplicateWarning }}
                </x-nx-callout>
            @endif
            <x-nx-input-text name="last_name" label="Nachname" wire:model="last_name" required />
            <x-nx-input-text name="first_name" label="Vorname" wire:model="first_name" required />
            <x-nx-input-date name="birth_date" label="Geburtsdatum" wire:model="birth_date" />
        </div>
        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-nx-button variant="ghost" wire:click="$set('showCreate', false)">Abbrechen</x-nx-button>
                <x-nx-button variant="primary" wire:click="create">Anlegen</x-nx-button>
            </div>
        </x-slot>
    </x-nx-modal>
</x-ui-page>
