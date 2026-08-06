<?php

namespace Platform\Patient\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Patient\Models\Lookup;
use Platform\Patient\Support\Lookups;
use Platform\Patient\Tools\Concerns\ResolvesPatientTeam;

class CreateLookupTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesPatientTeam;

    public function getName(): string
    {
        return 'patient.lookups.POST';
    }

    public function getDescription(): string
    {
        return 'POST /patient/lookups - Adds a lookup value for a patient master-data select field. REQUIRED: type (gender|marital_status|nationality|language|country|health_insurance), value. Optional: label, position, active (default true).';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id' => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'type' => ['type' => 'string', 'enum' => Lookups::TYPES, 'description' => 'Lookup type (REQUIRED).'],
                'value' => ['type' => 'string', 'description' => 'Value (REQUIRED).'],
                'label' => ['type' => 'string', 'description' => 'Optional: display label.'],
                'position' => ['type' => 'integer', 'description' => 'Optional: sort order.'],
                'active' => ['type' => 'boolean', 'description' => 'Optional: default true.'],
            ],
            'required' => ['type', 'value'],
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

            $type = (string) ($arguments['type'] ?? '');
            if (!in_array($type, Lookups::TYPES, true)) {
                return ToolResult::error('VALIDATION_ERROR', 'type is invalid.');
            }
            $value = trim((string) ($arguments['value'] ?? ''));
            if ($value === '') {
                return ToolResult::error('VALIDATION_ERROR', 'value is required.');
            }

            $existing = array_map('mb_strtolower', Lookups::optionsFor($type, $teamId));
            if (in_array(mb_strtolower($value), $existing, true)) {
                return ToolResult::error('DUPLICATE', 'Value already exists (default or team value).');
            }

            $lookup = Lookup::create([
                'team_id'  => $teamId,
                'type'     => $type,
                'value'    => $value,
                'label'    => isset($arguments['label']) && $arguments['label'] !== '' ? (string) $arguments['label'] : null,
                'position' => (int) ($arguments['position'] ?? 0),
                'active'   => array_key_exists('active', $arguments) ? (bool) $arguments['active'] : true,
            ]);

            return ToolResult::success([
                'id' => $lookup->id, 'type' => $lookup->type, 'value' => $lookup->value,
                'team_id' => $teamId, 'message' => "Lookup '{$lookup->value}' added to '{$type}'.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error creating lookup: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['patient', 'lookups', 'settings', 'create'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => false,
        ];
    }
}
