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
        {{-- Akte öffnen — der klinische Verlauf lebt im Akte-Modul; hier nur Stammdaten. --}}
        @if(\Illuminate\Support\Facades\Route::has('encounter.akte.show'))
            <a href="{{ route('encounter.akte.show', $patient->id) }}" wire:navigate class="block">
                <x-nx-card class="hover:bg-[color:var(--nx-hover)] transition-colors">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            @svg('heroicon-o-folder-open', 'w-6 h-6 text-[color:var(--nx-muted)]')
                            <div>
                                <div class="text-sm font-medium text-[color:var(--nx-text)]">Akte öffnen</div>
                                <div class="text-xs text-[color:var(--nx-muted)]">Verlauf: Termine, Vorsorge, Beschäftigung, Werte</div>
                            </div>
                        </div>
                        @svg('heroicon-o-arrow-right', 'w-5 h-5 text-[color:var(--nx-faint)]')
                    </div>
                </x-nx-card>
            </a>
        @endif

        {{-- Identität (Stammdaten) --}}
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

        {{-- Telefon (typisierte Mehrfach-Werte) --}}
        <x-nx-section icon="heroicon-o-phone" title="Telefon" :hint="$phoneNumbers->count()">
            <x-nx-card flush>
                <div class="divide-y divide-[color:var(--nx-line)]">
                    @foreach($phoneNumbers as $phone)
                        <div class="flex items-center gap-3 px-4 py-2.5" wire:key="phone-{{ $phone->id }}">
                            <button type="button" wire:click="setPrimaryPhone({{ $phone->id }})" title="Als primär markieren"
                                    class="shrink-0 {{ $phone->is_primary ? 'text-[color:var(--nx-accent)]' : 'text-[color:var(--nx-faint)] hover:text-[color:var(--nx-muted)]' }}">
                                @svg($phone->is_primary ? 'heroicon-s-star' : 'heroicon-o-star', 'w-4 h-4')
                            </button>
                            <span class="w-28 shrink-0 text-xs text-[color:var(--nx-muted)]">{{ $phone->phone_type ?? '—' }}</span>
                            <span class="flex-1 text-sm text-[color:var(--nx-text)]">{{ $phone->number }}</span>
                            <button type="button" wire:click="removePhone({{ $phone->id }})" wire:confirm="Nummer entfernen?"
                                    class="shrink-0 text-[color:var(--nx-faint)] hover:text-[color:var(--nx-danger)]">
                                @svg('heroicon-o-trash', 'w-4 h-4')
                            </button>
                        </div>
                    @endforeach
                    <div class="flex items-end gap-2 px-4 py-3 bg-[color:var(--nx-bg)]">
                        <div class="w-32 shrink-0">
                            <x-nx-input-select name="newPhone.phone_type" label="Typ" wire:model="newPhone.phone_type" :options="$phoneTypeOptions" nullable nullLabel="—" />
                        </div>
                        <div class="flex-1">
                            <x-nx-input-text name="newPhone.number" label="Nummer" wire:model="newPhone.number" wire:keydown.enter="addPhone" />
                        </div>
                        <x-nx-button variant="secondary" size="sm" wire:click="addPhone">@svg('heroicon-o-plus', 'w-4 h-4') Hinzufügen</x-nx-button>
                    </div>
                </div>
            </x-nx-card>
        </x-nx-section>

        {{-- E-Mail (typisierte Mehrfach-Werte) --}}
        <x-nx-section icon="heroicon-o-envelope" title="E-Mail" :hint="$emailAddresses->count()">
            <x-nx-card flush>
                <div class="divide-y divide-[color:var(--nx-line)]">
                    @foreach($emailAddresses as $email)
                        <div class="flex items-center gap-3 px-4 py-2.5" wire:key="email-{{ $email->id }}">
                            <button type="button" wire:click="setPrimaryEmail({{ $email->id }})" title="Als primär markieren"
                                    class="shrink-0 {{ $email->is_primary ? 'text-[color:var(--nx-accent)]' : 'text-[color:var(--nx-faint)] hover:text-[color:var(--nx-muted)]' }}">
                                @svg($email->is_primary ? 'heroicon-s-star' : 'heroicon-o-star', 'w-4 h-4')
                            </button>
                            <span class="w-28 shrink-0 text-xs text-[color:var(--nx-muted)]">{{ $email->email_type ?? '—' }}</span>
                            <span class="flex-1 text-sm text-[color:var(--nx-text)] truncate">{{ $email->email }}</span>
                            <button type="button" wire:click="removeEmail({{ $email->id }})" wire:confirm="E-Mail entfernen?"
                                    class="shrink-0 text-[color:var(--nx-faint)] hover:text-[color:var(--nx-danger)]">
                                @svg('heroicon-o-trash', 'w-4 h-4')
                            </button>
                        </div>
                    @endforeach
                    <div class="flex items-end gap-2 px-4 py-3 bg-[color:var(--nx-bg)]">
                        <div class="w-32 shrink-0">
                            <x-nx-input-select name="newEmail.email_type" label="Typ" wire:model="newEmail.email_type" :options="$emailTypeOptions" nullable nullLabel="—" />
                        </div>
                        <div class="flex-1">
                            <x-nx-input-text name="newEmail.email" label="E-Mail" wire:model="newEmail.email" wire:keydown.enter="addEmail" />
                        </div>
                        <x-nx-button variant="secondary" size="sm" wire:click="addEmail">@svg('heroicon-o-plus', 'w-4 h-4') Hinzufügen</x-nx-button>
                    </div>
                </div>
            </x-nx-card>
        </x-nx-section>

        {{-- Adressen (typisierte Mehrfach-Werte) --}}
        <x-nx-section icon="heroicon-o-map-pin" title="Adressen" :hint="$postalAddresses->count()">
            <x-nx-card flush>
                <div class="divide-y divide-[color:var(--nx-line)]">
                    @foreach($postalAddresses as $address)
                        <div class="flex items-center gap-3 px-4 py-2.5" wire:key="addr-{{ $address->id }}">
                            <button type="button" wire:click="setPrimaryAddress({{ $address->id }})" title="Als primär markieren"
                                    class="shrink-0 {{ $address->is_primary ? 'text-[color:var(--nx-accent)]' : 'text-[color:var(--nx-faint)] hover:text-[color:var(--nx-muted)]' }}">
                                @svg($address->is_primary ? 'heroicon-s-star' : 'heroicon-o-star', 'w-4 h-4')
                            </button>
                            <span class="w-28 shrink-0 text-xs text-[color:var(--nx-muted)]">{{ $address->address_type ?? '—' }}</span>
                            <span class="flex-1 text-sm text-[color:var(--nx-text)] truncate">
                                {{ trim(($address->street ? $address->street . ' ' . $address->house_number : '') . ', ' . trim(($address->postal_code ?? '') . ' ' . ($address->city ?? '')), ', ') ?: '—' }}
                            </span>
                            <button type="button" wire:click="removeAddress({{ $address->id }})" wire:confirm="Adresse entfernen?"
                                    class="shrink-0 text-[color:var(--nx-faint)] hover:text-[color:var(--nx-danger)]">
                                @svg('heroicon-o-trash', 'w-4 h-4')
                            </button>
                        </div>
                    @endforeach
                    <div class="px-4 py-3 bg-[color:var(--nx-bg)] space-y-2">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <x-nx-input-select name="newAddress.address_type" label="Typ" wire:model="newAddress.address_type" :options="$addressTypeOptions" nullable nullLabel="—" />
                            <x-nx-input-text name="newAddress.street" label="Straße" wire:model="newAddress.street" />
                            <x-nx-input-text name="newAddress.house_number" label="Nr." wire:model="newAddress.house_number" />
                            <x-nx-input-text name="newAddress.postal_code" label="PLZ" wire:model="newAddress.postal_code" />
                            <x-nx-input-text name="newAddress.city" label="Ort" wire:model="newAddress.city" />
                            <x-nx-input-select name="newAddress.country" label="Land" wire:model="newAddress.country" :options="$lookups['country']" nullable nullLabel="—" />
                        </div>
                        <div class="flex justify-end">
                            <x-nx-button variant="secondary" size="sm" wire:click="addAddress">@svg('heroicon-o-plus', 'w-4 h-4') Adresse hinzufügen</x-nx-button>
                        </div>
                    </div>
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
        @if(!empty($nav['lensKey']) && $nav['nodeId'] !== null)
            @include('patient::livewire.patient._context-sidebar', ['nav' => $nav, 'activePatientId' => $patient->id])
        @else
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
</x-ui-page>
