{{--
    Patient · Haupt-Sidebar (nx). Navigations-Linsen-fähig (Betrieb-first + Suche-Fallback).
--}}

<div>
    <div x-show="!collapsed" class="p-3 text-sm italic text-[var(--nx-text)] uppercase border-b border-[color:var(--nx-line)] mb-2">
        Patienten
    </div>

    <x-ui-sidebar-list>
        <x-ui-sidebar-item :href="route('patient.dashboard')" :active="request()->routeIs('patient.dashboard')">
            @svg('heroicon-o-home', 'w-4 h-4 text-[var(--nx-text)]')
            <span class="ml-2 text-sm">Dashboard</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('patient.settings')" :active="request()->routeIs('patient.settings')">
            @svg('heroicon-o-cog-6-tooth', 'w-4 h-4 text-[var(--nx-text)]')
            <span class="ml-2 text-sm">Einstellungen</span>
        </x-ui-sidebar-item>
    </x-ui-sidebar-list>

    {{-- Perspektive-Umschalter (nur wenn Linsen registriert sind) --}}
    @if(!empty($lenses))
        <div x-show="!collapsed" class="px-2 py-2 border-b border-[color:var(--nx-line)]">
            <div class="px-1 pb-1 text-xs font-medium tracking-wide text-[color:var(--nx-faint)]">Perspektive</div>
            <div class="flex flex-wrap gap-1">
                <a href="{{ route('patient.patients.index', ['lens' => 'search']) }}" wire:navigate
                   @class([
                       'inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs transition',
                       'bg-[color:var(--nx-active)] text-[color:var(--nx-text)] font-semibold' => !$activeLens,
                       'text-[color:var(--nx-muted)] hover:bg-[color:var(--nx-hover)]' => (bool) $activeLens,
                   ])>
                    @svg('heroicon-o-magnifying-glass', 'w-3.5 h-3.5')
                    <span>Suche</span>
                </a>
                @foreach($lenses as $l)
                    <a href="{{ route('patient.patients.index', ['lens' => $l->key()]) }}" wire:navigate
                       @class([
                           'inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs transition',
                           'bg-[color:var(--nx-active)] text-[color:var(--nx-text)] font-semibold' => $activeLensKey === $l->key(),
                           'text-[color:var(--nx-muted)] hover:bg-[color:var(--nx-hover)]' => $activeLensKey !== $l->key(),
                       ])>
                        @svg($l->icon(), 'w-3.5 h-3.5')
                        <span>{{ $l->label() }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($activeLens)
        {{-- Baum der aktiven Linse (führende Dimension) --}}
        @if(empty($tree))
            <div x-show="!collapsed" class="px-3 py-3 text-sm text-[color:var(--nx-muted)]">
                Keine Einträge in dieser Perspektive.
            </div>
        @else
            <x-ui-sidebar-list :label="$activeLens->label()">
                @foreach($tree as $node)
                    <x-ui-sidebar-item
                        :href="route('patient.patients.index', ['lens' => $activeLensKey, 'node' => $node['id']])"
                        :active="(string) $activeNode === (string) $node['id']">
                        <span class="flex items-center gap-2 min-w-0" style="padding-left: {{ ($node['depth'] ?? 0) * 0.75 }}rem">
                            @svg(($node['depth'] ?? 0) === 0 ? 'heroicon-o-building-office-2' : 'heroicon-o-building-office', 'w-4 h-4 text-[var(--nx-text)] shrink-0')
                            <span class="text-sm truncate">{{ $node['label'] }}</span>
                        </span>
                    </x-ui-sidebar-item>
                @endforeach
            </x-ui-sidebar-list>
        @endif
    @else
        {{-- Patient-first (fachneutraler Default / Fallback) --}}
        <x-ui-sidebar-list label="Patient">
            <x-ui-sidebar-item :href="route('patient.patients.index', ['lens' => 'search'])" :active="request()->routeIs('patient.patients.index')">
                @svg('heroicon-o-identification', 'w-4 h-4 text-[var(--nx-text)]')
                <span class="ml-2 text-sm">Patienten</span>
            </x-ui-sidebar-item>
        </x-ui-sidebar-list>

        @if($patients->isNotEmpty())
            <x-ui-sidebar-list label="Zuletzt">
                @foreach($patients as $patient)
                    <x-ui-sidebar-item :href="route('patient.patients.show', $patient->id)">
                        @svg('heroicon-o-user', 'w-4 h-4 text-[var(--nx-text)]')
                        <span class="ml-2 text-sm truncate">{{ $patient->getDisplayName() }}</span>
                    </x-ui-sidebar-item>
                @endforeach
            </x-ui-sidebar-list>
        @endif
    @endif

    <div x-show="collapsed" class="px-2 py-2 border-b border-[color:var(--nx-line)]">
        <div class="flex flex-col gap-2">
            <a href="{{ route('patient.dashboard') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--nx-text)] hover:bg-[var(--nx-bg)]">
                @svg('heroicon-o-home', 'w-5 h-5')
            </a>
            <a href="{{ route('patient.patients.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--nx-text)] hover:bg-[var(--nx-bg)]">
                @svg('heroicon-o-identification', 'w-5 h-5')
            </a>
        </div>
    </div>
</div>
