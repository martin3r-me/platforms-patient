<?php

namespace Platform\Patient\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Patient\Models\Lookup;
use Platform\Patient\Tools\Concerns\ResolvesPatientTeam;

class UpdateLookupTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesPatientTeam;

    public function getName(): string
    {
        return 'patient.lookups.PUT';
    }

    public function getDescription(): string
    {
        return 'PUT /patient/lookups - Updates a team lookup value. REQUIRED: lookup_id. Optional: value, label, position, active.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id' => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'lookup_id' => ['type' => 'integer', 'description' => 'Id of the lookup value (REQUIRED).'],
                'value' => ['type' => 'string', 'description' => 'Optional.'],
                'label' => ['type' => 'string', 'description' => 'Optional (empty string clears).'],
                'position' => ['type' => 'integer', 'description' => 'Optional.'],
                'active' => ['type' => 'boolean', 'description' => 'Optional.'],
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

            $payload = [];
            if (array_key_exists('value', $arguments) && $arguments['value'] !== null) {
                $value = trim((string) $arguments['value']);
                if ($value === '') {
                    return ToolResult::error('VALIDATION_ERROR', 'value must not be empty.');
                }
                $payload['value'] = $value;
            }
            if (array_key_exists('label', $arguments)) {
                $payload['label'] = ($arguments['label'] === '' || $arguments['label'] === null) ? null : (string) $arguments['label'];
            }
            if (array_key_exists('position', $arguments) && $arguments['position'] !== null) {
                $payload['position'] = (int) $arguments['position'];
            }
            if (array_key_exists('active', $arguments) && $arguments['active'] !== null) {
                $payload['active'] = (bool) $arguments['active'];
            }

            if (empty($payload)) {
                return ToolResult::error('NO_CHANGE', 'No changes provided.');
            }

            $lookup->update($payload);

            return ToolResult::success([
                'id' => $lookup->id, 'type' => $lookup->type, 'value' => $lookup->value,
                'team_id' => $teamId, 'message' => 'Lookup updated successfully.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error updating lookup: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['patient', 'lookups', 'settings', 'update'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => true,
        ];
    }
}
