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
        {{-- Einstieg links (Baum/Suche in der Sidebar); Main zeigt keinen Gesamt-Liste-Dump. --}}
        <x-nx-card>
            <x-nx-empty icon="heroicon-o-identification">
                Kein Patient ausgewählt. Wähle links über die Perspektive einen Betrieb bzw. Patienten
                — oder nutze die Suche in der Seitenleiste.
            </x-nx-empty>
        </x-nx-card>
    </x-ui-page-container>

    <x-slot name="sidebar">
        @if($sidebarMode === 'node')
            @include('patient::livewire.patient._context-sidebar', ['nav' => $nav])
        @elseif($sidebarMode === 'pick')
            <x-ui-page-sidebar title="Betrieb wählen" icon="heroicon-o-building-office-2" width="w-72" :defaultOpen="true">
                <div class="p-6 text-sm text-[color:var(--nx-muted)]">
                    Wähle links im Baum einen Betrieb bzw. eine Abteilung, um die Patientenliste zu sehen — oder wechsle die Perspektive auf „Suche".
                </div>
            </x-ui-page-sidebar>
        @else
            <x-ui-page-sidebar title="Patienten" icon="heroicon-o-identification" width="w-72" :defaultOpen="true">
                <div class="p-2 space-y-2">
                    <x-nx-input-text name="search" wire:model.live.debounce.300ms="search"
                                     placeholder="Name / Labor-Nr …" />
                    <div class="space-y-0.5">
                        @forelse($patients as $patient)
                            <a href="{{ route('patient.patients.show', ['patient' => $patient->id, 'lens' => 'search']) }}" wire:navigate
                               class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-[color:var(--nx-text)] hover:bg-[color:var(--nx-hover)]">
                                @svg('heroicon-o-user', 'w-4 h-4 text-[color:var(--nx-muted)] shrink-0')
                                <span class="min-w-0">
                                    <span class="block truncate">{{ $patient->getDisplayName() }}</span>
                                    <span class="block text-xs text-[color:var(--nx-faint)]">{{ optional($patient->birth_date)->format('d.m.Y') ?? '—' }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="px-2 py-3 text-sm text-[color:var(--nx-muted)]">Keine Treffer.</div>
                        @endforelse
                    </div>
                </div>
            </x-ui-page-sidebar>
        @endif
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
