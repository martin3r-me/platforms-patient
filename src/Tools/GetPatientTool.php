<?php

namespace Platform\Patient\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Patient\Models\Patient;
use Platform\Patient\Tools\Concerns\ResolvesPatientTeam;

class GetPatientTool implements ToolContract, ToolMetadataContract
{
    use ResolvesPatientTeam;

    public function getName(): string
    {
        return 'patient.patient.GET';
    }

    public function getDescription(): string
    {
        return 'GET /patient/patient - Shows a single patient. REQUIRED: patient_id. Note: confidential encrypted fields (social_security_number, notes) are NOT returned (Schweigepflicht).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: team id. Default: current team from context.',
                ],
                'patient_id' => [
                    'type' => 'integer',
                    'description' => 'Id of the patient (REQUIRED).',
                ],
            ],
            'required' => ['patient_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) {
                return $resolved['error'];
            }
            $teamId = (int) $resolved['team_id'];

            $patientId = (int) ($arguments['patient_id'] ?? 0);
            if ($patientId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'patient_id is required.');
            }

            $patient = Patient::query()->forTeam($teamId)->find($patientId);
            if (!$patient) {
                return ToolResult::error('NOT_FOUND', 'Patient not found (or no access).');
            }

            return ToolResult::success([
                'id' => $patient->id,
                'uuid' => $patient->uuid,
                'display_name' => $patient->getDisplayName(),
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'title' => $patient->title,
                'birth_name' => $patient->birth_name,
                'birth_date' => optional($patient->birth_date)->toDateString(),
                'birth_place' => $patient->birth_place,
                'gender' => $patient->gender,
                'nationality' => $patient->nationality,
                'marital_status' => $patient->marital_status,
                'language' => $patient->language,
                'country' => $patient->country,
                'deceased_at' => optional($patient->deceased_at)->toDateString(),
                'health_insurance' => $patient->health_insurance,
                'lab_number' => $patient->lab_number,
                'lab_number_external' => $patient->lab_number_external,
                'family_doctor' => $patient->family_doctor,
                'disability_degree' => $patient->disability_degree,
                'reduced_earning_capacity' => $patient->reduced_earning_capacity,
                'equal_status' => $patient->equal_status,
                'phone' => $patient->phone,
                'phone_private' => $patient->phone_private,
                'mobile' => $patient->mobile,
                'fax' => $patient->fax,
                'email_work' => $patient->email_work,
                'email_private' => $patient->email_private,
                'street' => $patient->street,
                'postal_code' => $patient->postal_code,
                'city' => $patient->city,
                'team_id' => $patient->team_id,
                'created_at' => $patient->created_at?->toISOString(),
                'updated_at' => $patient->updated_at?->toISOString(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error loading patient: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true,
            'category' => 'read',
            'tags' => ['patient', 'get'],
            'risk_level' => 'safe',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => true,
        ];
    }
}
