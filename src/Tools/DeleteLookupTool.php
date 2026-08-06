<?php

namespace Platform\Patient\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Patient\Models\Lookup;
use Platform\Patient\Tools\Concerns\ResolvesPatientTeam;

class DeleteLookupTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesPatientTeam;

    public function getName(): string
    {
        return 'patient.lookups.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /patient/lookups - Deletes a team lookup value (config defaults are unaffected). REQUIRED: lookup_id.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id' => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'lookup_id' => ['type' => 'integer', 'description' => 'Id of the lookup value (REQUIRED).'],
            ],
            'required' => ['lookup_id'],
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

            $id = (int) ($arguments['lookup_id'] ?? 0);
            if ($id <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'lookup_id is required.');
            }

            $lookup = Lookup::query()->forTeam($teamId)->find($id);
            if (!$lookup) {
                return ToolResult::error('NOT_FOUND', 'Lookup not found (or no access).');
            }

            $value = $lookup->value;
            $lookup->delete();

            return ToolResult::success(['id' => $id, 'message' => "Lookup '{$value}' deleted."]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error deleting lookup: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['patient', 'lookups', 'settings', 'delete'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => false,
        ];
    }
}
