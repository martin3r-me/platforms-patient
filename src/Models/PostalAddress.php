<?php

namespace Platform\Patient\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PostalAddress — typisierte Postadresse eines Patienten (eigenständig, keine CRM-Abhängigkeit).
 */
class PostalAddress extends Model
{
    protected $table = 'patient_postal_addresses';

    protected $fillable = [
        'team_id', 'patient_id', 'address_type',
        'street', 'house_number', 'postal_code', 'city', 'country', 'is_primary',
    ];

    protected $casts = ['is_primary' => 'boolean'];

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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
