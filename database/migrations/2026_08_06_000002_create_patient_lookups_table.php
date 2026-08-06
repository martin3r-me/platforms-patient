<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * patient_lookups — team-anpassbare Referenzlisten (Nationalität, Familienstand,
 * Sprache, Land, Krankenkasse …). Eine Tabelle für alle Listen, getrennt per `type`.
 * Bewusst EIGEN (kein CRM-Bezug) — patient bleibt fachneutrale, isolierte Wurzel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_lookups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('type', 64);   // marital_status | nationality | language | country | health_insurance
            $table->string('value');
            $table->string('label')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['team_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_lookups');
    }
};
