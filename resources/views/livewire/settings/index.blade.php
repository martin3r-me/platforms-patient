{{--
    Patient · Einstellungen — Lookup-Listen (nx-Design-System).
    Config-Defaults (immer da) + team-eigene Werte. Kein CRM-Bezug.
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Einstellungen" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Patienten', 'route' => 'patient.dashboard', 'icon' => 'identification'],
            ['label' => 'Einstellungen'],
        ]" />
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        <x-nx-section icon="heroicon-o-list-bullet" title="Referenzlisten"
                      description="Auswahlwerte für die Patienten-Stammdaten. Defaults sind immer verfügbar; hier ergänzt ihr team-eigene Werte.">
            {{-- Typ-Auswahl --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($types as $type)
                    <x-nx-button size="sm"
                                 :variant="$activeType === $type ? 'primary' : 'secondary'"
                                 wire:click="selectType('{{ $type }}')">
                        {{ $typeLabels[$type] ?? $type }}
                    </x-nx-button>
                @endforeach
            </div>

            <x-nx-card>
                <div class="flex items-center gap-2 mb-3">
                    <x-nx-input-text name="newValue" wire:model="newValue"
                                     wire:keydown.enter="add"
                                     placeholder="Neuen Wert hinzufügen …" class="flex-1" />
                    <x-nx-button variant="primary" size="sm" wire:click="add">
                        @svg('heroicon-o-plus', 'w-4 h-4')
                    </x-nx-button>
                </div>
                @error('newValue') <p class="text-xs text-[color:rgb(var(--ui-danger-rgb))] mb-2">{{ $message }}</p> @enderror

                @if($teamValues->isEmpty())
                    <div class="text-sm text-[color:var(--nx-muted)]">Keine Werte. Füge welche hinzu.</div>
                @else
                    <div class="divide-y divide-[color:var(--nx-line)]">
                        @foreach($teamValues as $lookup)
                            <div class="flex items-center justify-between py-2" wire:key="lk-{{ $lookup->id }}">
                                <span class="text-sm text-[color:var(--nx-text)]">
                                    {{ $lookup->value }}
                                    @unless($lookup->active)
                                        <x-nx-badge size="xs">inaktiv</x-nx-badge>
                                    @endunless
                                </span>
                                <x-nx-button variant="danger" size="xs" wire:click="delete({{ $lookup->id }})"
                                             wire:confirm="Wert entfernen?">
                                    @svg('heroicon-o-trash', 'w-4 h-4')
                                </x-nx-button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-nx-card>
        </x-nx-section>
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Einstellungen" width="w-80" :defaultOpen="true">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-3">Referenzlisten</h3>
                    <div class="text-sm text-[color:var(--nx-muted)]">Familienstand · Nationalität · Sprache · Land · Krankenkasse</div>
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
