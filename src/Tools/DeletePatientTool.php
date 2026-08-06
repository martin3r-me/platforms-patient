<?php

namespace Platform\Patient\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Patient\Models\Patient;
use Platform\Patient\Tools\Concerns\ResolvesPatientTeam;

class DeletePatientTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesPatientTeam;

    public function getName(): string
    {
        return 'patient.patients.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /patient/patients - Deletes a patient (soft-delete). REQUIRED: patient_id.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
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
        ]);
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

            $name = $patient->getDisplayName();
            $patient->delete();

            return ToolResult::success([
                'id' => $patientId,
                'message' => "Patient '{$name}' deleted.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error deleting patient: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['patient', 'patients', 'delete'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
