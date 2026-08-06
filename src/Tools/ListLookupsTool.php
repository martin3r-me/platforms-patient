<?php

namespace Platform\Patient\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Patient\Models\Lookup;
use Platform\Patient\Support\Lookups;
use Platform\Patient\Tools\Concerns\ResolvesPatientTeam;

class ListLookupsTool implements ToolContract, ToolMetadataContract
{
    use ResolvesPatientTeam;

    public function getName(): string
    {
        return 'patient.lookups.GET';
    }

    public function getDescription(): string
    {
        return 'GET /patient/lookups - Lists the team lookup values (DB-backed, seeded with defaults on first access) for patient master-data select fields. Types: gender, marital_status, nationality, language, country, health_insurance.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'type' => ['type' => 'string', 'enum' => Lookups::TYPES, 'description' => 'Optional: filter by lookup type.'],
            ],
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

            Lookups::ensureSeeded($teamId);

            $query = Lookup::query()->forTeam($teamId);
            if (isset($arguments['type'])) {
                if (!in_array($arguments['type'], Lookups::TYPES, true)) {
                    return ToolResult::error('VALIDATION_ERROR', 'type is invalid.');
                }
                $query->where('type', $arguments['type']);
            }

            $teamValues = $query->orderBy('type')->orderBy('position')->orderBy('value')->get()
                ->map(fn (Lookup $l) => [
                    'id' => $l->id, 'type' => $l->type, 'value' => $l->value,
                    'label' => $l->label, 'position' => $l->position, 'active' => (bool) $l->active,
                ])->values()->toArray();

            $effective = [];
            $types = isset($arguments['type']) ? [$arguments['type']] : Lookups::TYPES;
            foreach ($types as $t) {
                $effective[$t] = Lookups::optionsFor($t, $teamId);
            }

            return ToolResult::success([
                'team_values' => $teamValues,
                'effective_options' => $effective,
                'team_id' => $teamId,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error loading lookups: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true, 'category' => 'read', 'tags' => ['patient', 'lookups', 'settings', 'list'],
            'risk_level' => 'safe', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => true,
        ];
    }
}
