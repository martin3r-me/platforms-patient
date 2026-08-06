<?php

namespace Platform\Patient\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Patient\Models\Patient;
use Platform\Patient\Tools\Concerns\ResolvesPatientTeam;
use Platform\Patient\Tools\Concerns\HasPatientFields;

class UpdatePatientTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesPatientTeam;
    use HasPatientFields;

    public function getName(): string
    {
        return 'patient.patients.PUT';
    }

    public function getDescription(): string
    {
        return 'PUT /patient/patients - Updates a patient master record. REQUIRED: patient_id. Any demographic/contact/insurance field is optional (empty string clears it).';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => array_merge(
                [
                    'team_id' => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                    'patient_id' => ['type' => 'integer', 'description' => 'Id of the patient (REQUIRED).'],
                ],
                $this->fieldProperties()
            ),
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

            $payload = $this->buildPayload($arguments);
            if (empty($payload)) {
                return ToolResult::error('NO_CHANGE', 'No changes provided.');
            }

            $patient->update($payload);

            return ToolResult::success([
                'id' => $patient->id,
                'uuid' => $patient->uuid,
                'display_name' => $patient->getDisplayName(),
                'team_id' => $patient->team_id,
                'message' => "Patient '{$patient->getDisplayName()}' updated successfully.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error updating patient: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['patient', 'patients', 'update'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => true,
        ];
    }
}
