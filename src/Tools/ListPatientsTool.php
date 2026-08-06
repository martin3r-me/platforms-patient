<?php

namespace Platform\Patient\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Patient\Models\Patient;
use Platform\Patient\Tools\Concerns\ResolvesPatientTeam;

class ListPatientsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;
    use ResolvesPatientTeam;

    public function getName(): string
    {
        return 'patient.patients.GET';
    }

    public function getDescription(): string
    {
        return 'GET /patient/patients - Lists patients (isolated master data). Params: team_id (optional), search (name/lab_number), sort/limit/offset (optional). Encrypted fields (social_security_number, notes) are NOT returned in the list.';
    }

    public function getSchema(): array
    {
        return $this->mergeSchemas(
            $this->getStandardGetSchema(),
            [
                'properties' => [
                    'team_id' => [
                        'type' => 'integer',
                        'description' => 'Optional: team id. Default: current team from context.',
                    ],
                ],
            ]
        );
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) {
                return $resolved['error'];
            }
            $teamId = (int) $resolved['team_id'];

            $query = Patient::query()->forTeam($teamId);

            $this->applyStandardFilters($query, $arguments, ['gender', 'city', 'created_at', 'updated_at']);
            $this->applyStandardSearch($query, $arguments, ['first_name', 'last_name', 'lab_number', 'lab_number_external']);
            $this->applyStandardSort($query, $arguments, ['last_name', 'first_name', 'birth_date', 'created_at', 'updated_at'], 'last_name', 'asc');

            $result = $this->applyStandardPaginationResult($query, $arguments);

            $data = collect($result['data'])->map(fn (Patient $p) => [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'display_name' => $p->getDisplayName(),
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'birth_date' => optional($p->birth_date)->toDateString(),
                'gender' => $p->gender,
                'lab_number' => $p->lab_number,
                'team_id' => $p->team_id,
            ])->values()->toArray();

            return ToolResult::success([
                'data' => $data,
                'pagination' => $result['pagination'] ?? null,
                'team_id' => $teamId,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error loading patients: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true,
            'category' => 'read',
            'tags' => ['patient', 'patients', 'list'],
            'risk_level' => 'safe',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => true,
        ];
    }
}
