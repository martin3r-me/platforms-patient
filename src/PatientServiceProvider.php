<?php

namespace Platform\Patient;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Platform\Core\PlatformCore;
use Platform\Core\Routing\ModuleRouter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PatientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/patient.php', 'patient');

        // Akte-Panel-Registry: Fachmodule (encounter, occupational, …) docken Panels an.
        $this->app->singleton(\Platform\Patient\Services\PatientPanelRegistry::class);

        // Navigations-Linsen-Registry: Fachmodule bringen führende Dimensionen mit (Betrieb, …).
        $this->app->singleton(\Platform\Patient\Services\PatientNavigationRegistry::class);
    }

    public function boot(): void
    {
        if (
            config()->has('patient.routing') &&
            config()->has('patient.navigation') &&
            Schema::hasTable('modules')
        ) {
            PlatformCore::registerModule([
                'key'        => 'patient',
                'title'      => 'Patienten',
                'group'      => 'clinical',
                'routing'    => config('patient.routing'),
                'guard'      => config('patient.guard'),
                'navigation' => config('patient.navigation'),
                'sidebar'    => config('patient.sidebar'),
            ]);
        }

        if (PlatformCore::getModule('patient')) {
            ModuleRouter::group('patient', function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/patient.php' => config_path('patient.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'patient');

        $this->registerLivewireComponents();

        $this->registerTools();
    }

    /**
     * Registriert die MCP/LLM-Tools des Moduls.
     */
    protected function registerTools(): void
    {
        try {
            $registry = resolve(\Platform\Core\Tools\ToolRegistry::class);

            $registry->register(new \Platform\Patient\Tools\ListPatientsTool());
            $registry->register(new \Platform\Patient\Tools\GetPatientTool());
            $registry->register(new \Platform\Patient\Tools\CreatePatientTool());
            $registry->register(new \Platform\Patient\Tools\UpdatePatientTool());
            $registry->register(new \Platform\Patient\Tools\DeletePatientTool());

            // Settings: Lookups (Referenzlisten)
            $registry->register(new \Platform\Patient\Tools\ListLookupsTool());
            $registry->register(new \Platform\Patient\Tools\CreateLookupTool());
            $registry->register(new \Platform\Patient\Tools\UpdateLookupTool());
            $registry->register(new \Platform\Patient\Tools\DeleteLookupTool());
        } catch (\Throwable $e) {
            // ToolRegistry nicht verfügbar (z. B. in bestimmten CLI-Kontexten) — ignorieren.
        }
    }

    /**
     * Registriert alle Livewire-Komponenten unter src/Livewire/ rekursiv.
     * Datei src/Livewire/Patient/Index.php → Alias patient.patient.index
     */
    protected function registerLivewireComponents(): void
    {
        $basePath = __DIR__ . '/Livewire';
        $baseNamespace = 'Platform\\Patient\\Livewire';
        $prefix = 'patient';

        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = $baseNamespace . '\\' . $classPath;

            if (!class_exists($class)) {
                continue;
            }

            $aliasPath = str_replace(['\\', '/'], '.', Str::kebab(str_replace('.php', '', $relativePath)));
            $alias = $prefix . '.' . $aliasPath;

            Livewire::component($alias, $class);
        }
    }
}
