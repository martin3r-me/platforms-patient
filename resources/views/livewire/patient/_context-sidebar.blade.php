{{--
    Innere Seiten-Sidebar: Patientenliste des gewählten Navigations-Knotens (Betrieb …)
    — oder ein Hinweis, wenn kein Knoten gewählt ist. Erwartet: $nav, optional $activePatientId.
--}}
@php($activePatientId = $activePatientId ?? null)

@if(!empty($nav['lensKey']) && $nav['nodeId'] !== null)
    <x-ui-page-sidebar :title="$nav['nodeLabel'] ?? 'Patienten'" icon="heroicon-o-user-group" width="w-72" :defaultOpen="true">
        <nav class="p-2 space-y-0.5">
            <a href="{{ route('patient.patients.index', ['lens' => $nav['lensKey']]) }}" wire:navigate
               class="flex items-center gap-2 px-2 py-1.5 text-xs text-[color:var(--nx-muted)] hover:text-[color:var(--nx-text)]">
                @svg('heroicon-o-chevron-left', 'w-3.5 h-3.5')
                <span>Zurück zur Auswahl</span>
            </a>

            @forelse($nav['patients'] as $p)
                <a href="{{ $p['url'] }}" wire:navigate
                   @class([
                       'flex items-center gap-2 px-2 py-1.5 rounded-md text-sm transition',
                       'bg-[color:var(--nx-active)] text-[color:var(--nx-text)] font-semibold' => (int) $activePatientId === (int) $p['patient_id'],
                       'text-[color:var(--nx-text)] hover:bg-[color:var(--nx-hover)]' => (int) $activePatientId !== (int) $p['patient_id'],
                   ])>
                    @svg('heroicon-o-user', 'w-4 h-4 text-[color:var(--nx-muted)] shrink-0')
                    <span class="min-w-0">
                        <span class="block truncate">{{ $p['name'] }}</span>
                        @if(!empty($p['subtitle']))
                            <span class="block text-xs text-[color:var(--nx-faint)] truncate">{{ $p['subtitle'] }}</span>
                        @endif
                    </span>
                </a>
            @empty
                <div class="px-2 py-3 text-sm text-[color:var(--nx-muted)]">Keine Patienten an diesem Knoten.</div>
            @endforelse
        </nav>
    </x-ui-page-sidebar>
@else
    <x-ui-page-sidebar title="Übersicht" width="w-72" :defaultOpen="true">
        <div class="p-6 text-sm text-[color:var(--nx-muted)]">
            Wähle links eine Perspektive und einen Eintrag (z. B. einen Betrieb), um die Patientenliste zu sehen — oder nutze die Suche.
        </div>
    </x-ui-page-sidebar>
@endif
