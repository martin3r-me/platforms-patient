<?php

namespace Platform\Patient\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Lookup — team-anpassbare Referenzliste (per `type`). Erweitert die Config-Defaults.
 *
 * @ai.description Team-Lookup-Wert (Nationalität, Familienstand, Krankenkasse …).
 */
class Lookup extends Model
{
    protected $table = 'patient_lookups';

    protected $fillable = [
        'team_id',
        'type',
        'value',
        'label',
        'position',
        'active',
    ];

    protected $casts = [
        'position' => 'integer',
        'active'   => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->team_id) && auth()->check()) {
                $model->team_id = auth()->user()->currentTeam?->id;
            }
        });
    }

    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where($this->getTable() . '.team_id', $teamId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
