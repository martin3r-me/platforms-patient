<?php

namespace Platform\Patient\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;
use Platform\Core\Contracts\HasDisplayName;

/**
 * Patient — fachneutrale, isolierte Patienten-Stammdaten (Schweigepflicht/DSGVO).
 *
 * Kennt KEINE Fachlogik und KEINEN Arbeitgeber-Bezug. Fachmodule (occupational, …)
 * und spätere Schienen (encounter, lab, devices) referenzieren diesen Patienten lose.
 *
 * @ai.description Fachneutraler Patienten-Stammdatensatz (isoliert, teilverschlüsselt).
 */
class Patient extends Model implements HasDisplayName
{
    use SoftDeletes;

    protected $table = 'patient_records';

    protected $fillable = [
        'uuid',
        'team_id',
        'first_name',
        'last_name',
        'title',
        'birth_name',
        'birth_date',
        'birth_place',
        'gender',
        'nationality',
        'marital_status',
        'language',
        'country',
        'deceased_at',
        'health_insurance',
        'social_security_number',
        'lab_number',
        'lab_number_external',
        'family_doctor',
        'disability_degree',
        'reduced_earning_capacity',
        'equal_status',
        'phone',
        'phone_private',
        'mobile',
        'fax',
        'email_work',
        'email_private',
        'street',
        'postal_code',
        'city',
        'notes',
    ];

    protected $casts = [
        'birth_date'               => 'date',
        'deceased_at'              => 'date',
        'disability_degree'        => 'integer',
        'reduced_earning_capacity' => 'integer',
        'equal_status'             => 'boolean',
        // Schweigepflicht: sensible Felder at-rest verschlüsselt.
        'social_security_number'   => 'encrypted',
        'notes'                    => 'encrypted',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                do {
                    $uuid = (string) UuidV7::generate();
                } while (self::withTrashed()->where('uuid', $uuid)->exists());
                $model->uuid = $uuid;
            }

            if (empty($model->team_id) && auth()->check()) {
                $model->team_id = auth()->user()->currentTeam?->id;
            }
        });
    }

    /**
     * Team-Scope — jede Top-Level-Query eines team-gebundenen Models nutzt forTeam().
     */
    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where($this->getTable() . '.team_id', $teamId);
    }

    /**
     * Dubletten-Erkennung: gleicher Name + Geburtsdatum im Team.
     */
    public function scopeMatching(Builder $query, ?string $firstName, ?string $lastName, $birthDate): Builder
    {
        return $query
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->where('birth_date', $birthDate);
    }

    public function getDisplayName(): ?string
    {
        $name = trim(trim((string) $this->last_name) . ', ' . trim((string) $this->first_name), ', ');

        return $name !== '' ? $name : ('Patient #' . $this->id);
    }

    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(PhoneNumber::class, 'patient_id');
    }

    public function emailAddresses(): HasMany
    {
        return $this->hasMany(EmailAddress::class, 'patient_id');
    }

    public function postalAddresses(): HasMany
    {
        return $this->hasMany(PostalAddress::class, 'patient_id');
    }
}
