<?php

namespace Platform\Patient\Tools\Concerns;

/**
 * Gemeinsame Feld-Definitionen für Create/Update-Patient-Tools.
 */
trait HasPatientFields
{
    /** @var array<string,string> field => json type */
    protected array $patientFieldTypes = [
        'first_name' => 'string',
        'last_name' => 'string',
        'title' => 'string',
        'birth_name' => 'string',
        'birth_date' => 'string',
        'birth_place' => 'string',
        'gender' => 'string',
        'nationality' => 'string',
        'marital_status' => 'string',
        'language' => 'string',
        'country' => 'string',
        'deceased_at' => 'string',
        'health_insurance' => 'string',
        'social_security_number' => 'string',
        'lab_number' => 'string',
        'lab_number_external' => 'string',
        'family_doctor' => 'string',
        'disability_degree' => 'integer',
        'reduced_earning_capacity' => 'integer',
        'equal_status' => 'boolean',
        'phone' => 'string',
        'phone_private' => 'string',
        'mobile' => 'string',
        'fax' => 'string',
        'email_work' => 'string',
        'email_private' => 'string',
        'street' => 'string',
        'postal_code' => 'string',
        'city' => 'string',
        'notes' => 'string',
    ];

    protected function fieldProperties(): array
    {
        $props = [];
        foreach ($this->patientFieldTypes as $field => $type) {
            $desc = "Optional: {$field}.";
            if (in_array($field, ['birth_date', 'deceased_at'], true)) {
                $desc = "Optional: {$field} (date, YYYY-MM-DD).";
            }
            if (in_array($field, ['social_security_number', 'notes'], true)) {
                $desc = "Optional: {$field} (stored encrypted).";
            }
            $props[$field] = ['type' => $type, 'description' => $desc];
        }
        return $props;
    }

    /**
     * Baut aus den Argumenten ein Payload — nur übergebene Felder, leere Strings → null.
     */
    protected function buildPayload(array $arguments): array
    {
        $payload = [];
        foreach ($this->patientFieldTypes as $field => $type) {
            if (!array_key_exists($field, $arguments)) {
                continue;
            }
            $value = $arguments[$field];

            if ($value === '' || $value === null) {
                $payload[$field] = null;
                continue;
            }

            $payload[$field] = match ($type) {
                'integer' => (int) $value,
                'boolean' => (bool) $value,
                default   => trim((string) $value),
            };
        }
        return $payload;
    }
}
