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

class CreatePatientTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesPatientTeam;
    use HasPatientFields;

    public function getName(): string
    {
        return 'patient.patients.POST';
    }

    public function getDescription(): string
    {
        return 'POST /patient/patients - Creates an isolated patient master record. REQUIRED: last_name. Optional: first_name, birth_date and further demographic/contact/insurance fields. Runs a duplicate check on (first_name, last_name, birth_date).';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => array_merge(
                ['team_id' => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.']],
                $this->fieldProperties()
            ),
            'required' => ['last_name'],
        ]);
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            if (!$context->user) {
                return ToolResult::error('AUTH_ERROR', 'No user in context.');
            }

            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) {
                return $resolved['error'];
            }
            $teamId = (int) $resolved['team_id'];

            $lastName = trim((string) ($arguments['last_name'] ?? ''));
            if ($lastName === '') {
                return ToolResult::error('VALIDATION_ERROR', 'last_name is required.');
            }

            $payload = $this->buildPayload($arguments);
            $payload['team_id'] = $teamId;

            // Dubletten-Check (Name + Geburtsdatum)
            $duplicate = Patient::query()
                ->forTeam($teamId)
                ->matching($payload['first_name'] ?? null, $payload['last_name'] ?? null, $payload['birth_date'] ?? null)
                ->first();
            if ($duplicate) {
                return ToolResult::error('DUPLICATE', "A patient with the same name and birth date already exists (id {$duplicate->id}).");
            }

            $patient = Patient::create($payload);

            return ToolResult::success([
                'id' => $patient->id,
                'uuid' => $patient->uuid,
                'display_name' => $patient->getDisplayName(),
                'team_id' => $patient->team_id,
                'message' => "Patient '{$patient->getDisplayName()}' created successfully.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error creating patient: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['patient', 'patients', 'create'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
