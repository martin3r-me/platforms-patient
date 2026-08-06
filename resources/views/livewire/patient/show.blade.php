{{--
    Patient · Detail/Bearbeiten — nx-Design-System.
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar :title="$patient->getDisplayName()" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Patienten', 'route' => 'patient.patients.index', 'icon' => 'identification'],
            ['label' => $patient->getDisplayName()],
        ]">
            <x-nx-button variant="primary" size="sm" wire:click="save">
                @svg('heroicon-o-check', 'w-4 h-4')
                <span>Speichern</span>
            </x-nx-button>
            <x-nx-button variant="danger" size="sm" wire:click="delete"
                         wire:confirm="Diesen Patienten wirklich löschen?">
                @svg('heroicon-o-trash', 'w-4 h-4')
                <span>Löschen</span>
            </x-nx-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        {{-- Akte: von Fachmodulen beigesteuerte Panels (Termine, Beschäftigung, …) --}}
        @foreach($panels as $panel)
            <x-nx-section :icon="'heroicon-o-' . ($panel['icon'] ?? 'squares-2x2')"
                          :title="$panel['title']" :hint="count($panel['items'] ?? [])">
                @if(!empty($panel['actions']))
                    <x-slot name="action">
                        @foreach($panel['actions'] as $action)
                            <x-nx-button variant="secondary" size="sm" :href="$action['url']" wire:navigate>
                                {{ $action['label'] }}
                            </x-nx-button>
                        @endforeach
                    </x-slot>
                @endif
                @if(empty($panel['items']))
                    <x-nx-card>
                        <x-nx-empty :icon="'heroicon-o-' . ($panel['icon'] ?? 'squares-2x2')">
                            {{ $panel['empty'] ?? 'Keine Einträge.' }}
                        </x-nx-empty>
                    </x-nx-card>
                @else
                    <x-nx-card flush class="divide-y divide-[color:var(--nx-line)]">
                        @foreach($panel['items'] as $item)
                            <x-nx-list-item :href="$item['url'] ?? null"
                                            :icon="'heroicon-o-' . ($panel['icon'] ?? 'squares-2x2')"
                                            :title="$item['title'] ?? '—'"
                                            :subtitle="$item['subtitle'] ?? null"
                                            :meta="$item['meta'] ?? null" />
                        @endforeach
                    </x-nx-card>
                @endif
            </x-nx-section>
        @endforeach

        {{-- Identität --}}
        <x-nx-section icon="heroicon-o-identification" title="Identität">
            <x-nx-card>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-nx-input-text name="form.last_name" label="Nachname" wire:model="form.last_name" />
                    <x-nx-input-text name="form.first_name" label="Vorname" wire:model="form.first_name" />
                    <x-nx-input-text name="form.title" label="Titel" wire:model="form.title" />
                    <x-nx-input-text name="form.birth_name" label="Geburtsname" wire:model="form.birth_name" />
                    <x-nx-input-date name="form.birth_date" label="Geburtsdatum" wire:model="form.birth_date" />
                    <x-nx-input-text name="form.birth_place" label="Geburtsort" wire:model="form.birth_place" />
                    <x-nx-input-select name="form.gender" label="Geschlecht" wire:model="form.gender" :options="$lookups['gender']" nullable nullLabel="—" />
                    <x-nx-input-select name="form.nationality" label="Nationalität" wire:model="form.nationality" :options="$lookups['nationality']" nullable nullLabel="—" />
                    <x-nx-input-select name="form.marital_status" label="Familienstand" wire:model="form.marital_status" :options="$lookups['marital_status']" nullable nullLabel="—" />
                    <x-nx-input-select name="form.language" label="Sprache" wire:model="form.language" :options="$lookups['language']" nullable nullLabel="—" />
                    <x-nx-input-select name="form.country" label="Land" wire:model="form.country" :options="$lookups['country']" nullable nullLabel="—" />
                    <x-nx-input-date name="form.deceased_at" label="Verstorben am" wire:model="form.deceased_at" />
                </div>
            </x-nx-card>
        </x-nx-section>

        {{-- Kontakt --}}
        <x-nx-section icon="heroicon-o-phone" title="Kontakt">
            <x-nx-card>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-nx-input-text name="form.phone" label="Telefon" wire:model="form.phone" />
                    <x-nx-input-text name="form.phone_private" label="Telefon (privat)" wire:model="form.phone_private" />
                    <x-nx-input-text name="form.mobile" label="Mobil" wire:model="form.mobile" />
                    <x-nx-input-text name="form.fax" label="Fax" wire:model="form.fax" />
                    <x-nx-input-text name="form.email_work" label="E-Mail (beruflich)" wire:model="form.email_work" />
                    <x-nx-input-text name="form.email_private" label="E-Mail (privat)" wire:model="form.email_private" />
                </div>
            </x-nx-card>
        </x-nx-section>

        {{-- Adresse --}}
        <x-nx-section icon="heroicon-o-map-pin" title="Adresse">
            <x-nx-card>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-nx-input-text name="form.street" label="Straße" wire:model="form.street" />
                    <x-nx-input-text name="form.postal_code" label="PLZ" wire:model="form.postal_code" />
                    <x-nx-input-text name="form.city" label="Ort" wire:model="form.city" />
                </div>
            </x-nx-card>
        </x-nx-section>

        {{-- Versicherung & Kennungen --}}
        <x-nx-section icon="heroicon-o-identification" title="Versicherung & Kennungen">
            <x-nx-card>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-nx-input-select name="form.health_insurance" label="Krankenkasse" wire:model="form.health_insurance" :options="$lookups['health_insurance']" nullable nullLabel="—" />
                    <x-nx-input-text name="form.social_security_number" label="Sozialversicherungs-Nr" wire:model="form.social_security_number" />
                    <x-nx-input-text name="form.lab_number" label="Labor-Nr" wire:model="form.lab_number" />
                    <x-nx-input-text name="form.lab_number_external" label="Labor-Nr (extern)" wire:model="form.lab_number_external" />
                    <x-nx-input-text name="form.family_doctor" label="Hausarzt" wire:model="form.family_doctor" />
                </div>
            </x-nx-card>
        </x-nx-section>

        {{-- Schwerbehinderung --}}
        <x-nx-section icon="heroicon-o-hand-raised" title="Schwerbehinderung">
            <x-nx-card>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-nx-input-select name="form.disability_degree" label="GdB" wire:model="form.disability_degree" :options="$gdbSteps" nullable nullLabel="—" />
                    <x-nx-input-select name="form.reduced_earning_capacity" label="MdE" wire:model="form.reduced_earning_capacity" :options="$gdbSteps" nullable nullLabel="—" />
                    <x-nx-input-checkbox name="form.equal_status" label="Gleichstellung" wire:model="form.equal_status" />
                </div>
            </x-nx-card>
        </x-nx-section>

        {{-- Vertraulich --}}
        <x-nx-section icon="heroicon-o-lock-closed" title="Vertraulich"
                      description="Verschlüsselt gespeichert (Schweigepflicht).">
            <x-nx-card>
                <x-nx-input-textarea name="form.notes" label="Notizen" wire:model="form.notes" rows="5" />
            </x-nx-card>
        </x-nx-section>
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-3">Stammdaten</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-[color:var(--nx-muted)]">UUID</dt>
                            <dd class="truncate text-[color:var(--nx-text)]">{{ \Illuminate\Support\Str::limit($patient->uuid, 13) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-[color:var(--nx-muted)]">Angelegt</dt>
                            <dd class="text-[color:var(--nx-text)]">{{ optional($patient->created_at)->format('d.m.Y') }}</dd>
                        </div>
                    </dl>
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
