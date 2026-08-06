<?php

use Platform\Patient\Livewire\Dashboard;
use Platform\Patient\Livewire\Patient\Index as PatientIndex;
use Platform\Patient\Livewire\Patient\Show as PatientShow;
use Platform\Patient\Livewire\Settings\Index as SettingsIndex;

/*
 * Patient — Web-Routes (Prefix 'patient' aus config).
 */

Route::get('/', Dashboard::class)->name('patient.dashboard');
Route::get('/patients', PatientIndex::class)->name('patient.patients.index');
Route::get('/patients/{patient}', PatientShow::class)->name('patient.patients.show');
Route::get('/settings', SettingsIndex::class)->name('patient.settings');
