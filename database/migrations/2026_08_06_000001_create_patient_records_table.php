<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * patient_records — fachneutrale, isolierte Patienten-Stammdaten.
 *
 * Bewusst OHNE Arbeitgeber-/Firmen-Bezug (das ist Employment im occupational-Modul).
 * Sensible Freitext-/PII-Felder werden auf Model-Ebene verschlüsselt (Schweigepflicht):
 * notes, social_security_number. lab_number bleibt Klartext + indiziert (Import-Matching),
 * Dubletten-Erkennung über first_name + last_name + birth_date (Klartext).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();

            // Identität
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('title')->nullable();              // Titel
            $table->string('birth_name')->nullable();         // Geburtsname
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();        // Geburtsort
            $table->string('gender', 50)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('marital_status', 50)->nullable(); // Familienstand
            $table->string('language', 50)->nullable();
            $table->string('country')->nullable();
            $table->date('deceased_at')->nullable();          // verstorben am

            // Versicherung / Kennungen
            $table->string('health_insurance')->nullable();          // Krankenkasse
            $table->text('social_security_number')->nullable();      // SV-Nr (verschlüsselt)
            $table->string('lab_number', 64)->nullable();            // Labor-Nr (Import-Matching)
            $table->string('lab_number_external', 64)->nullable();
            $table->string('family_doctor')->nullable();             // Hausarzt

            // Schwerbehinderung
            $table->unsignedSmallInteger('disability_degree')->nullable();        // GdB
            $table->unsignedSmallInteger('reduced_earning_capacity')->nullable(); // MdE
            $table->boolean('equal_status')->default(false);                      // Gleichstellung

            // Kontakt
            $table->string('phone')->nullable();
            $table->string('phone_private')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();
            $table->string('email_work')->nullable();
            $table->string('email_private')->nullable();

            // Adresse
            $table->string('street')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();

            // Vertraulicher Freitext (verschlüsselt)
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('team_id');
            $table->index('lab_number');
            $table->index('lab_number_external');
            $table->index(['team_id', 'last_name', 'first_name', 'birth_date'], 'patient_records_dedup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_records');
    }
};
