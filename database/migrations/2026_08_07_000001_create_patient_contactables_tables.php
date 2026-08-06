<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Typisierte Mehrfach-Kontaktdaten je Patient — eigenständig in patient (KEINE CRM-Abhängigkeit,
 * Schweigepflicht/Isolation). Muster wie CRM (Typ aus Lookup + is_primary), aber eigene Tabellen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_postal_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->string('address_type')->nullable();   // Lookup
            $table->string('street')->nullable();
            $table->string('house_number', 32)->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('patient_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->string('phone_type')->nullable();      // Lookup
            $table->string('number');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('patient_email_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->string('email_type')->nullable();      // Lookup
            $table->string('email');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_email_addresses');
        Schema::dropIfExists('patient_phone_numbers');
        Schema::dropIfExists('patient_postal_addresses');
    }
};
