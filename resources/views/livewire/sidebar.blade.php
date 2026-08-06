{{--
    Patient · Sidebar (nx-Design-System). Nur var(--nx-*) Tokens.
--}}

<div>
    <div x-show="!collapsed" class="p-3 text-sm italic text-[var(--nx-text)] uppercase border-b border-[color:var(--nx-line)] mb-2">
        Patienten
    </div>

    <x-ui-sidebar-list label="Patient">
        <x-ui-sidebar-item :href="route('patient.dashboard')">
            @svg('heroicon-o-home', 'w-4 h-4 text-[var(--nx-text)]')
            <span class="ml-2 text-sm">Dashboard</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('patient.patients.index')">
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
