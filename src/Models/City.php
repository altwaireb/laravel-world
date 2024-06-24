<?php

namespace Altwaireb\World\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property int $state_id
 * @property string $latitude
 * @property string $longitude
 * @property bool $is_active
 */
class City extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id', 'name', 'country_id', 'state_id',
        'latitude', 'longitude', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active Cities.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', 1);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
