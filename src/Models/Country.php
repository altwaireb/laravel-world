<?php

namespace Altwaireb\World\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $iso2
 * @property string $iso3
 * @property string $numeric_code
 * @property string $phonecode
 * @property string $capital
 * @property string $currency
 * @property string $currency_name
 * @property string $currency_symbol
 * @property string $tld
 * @property string $native
 * @property string $region
 * @property string $subregion
 * @property array $timezones
 * @property array $translations
 * @property string $latitude
 * @property string $longitude
 * @property string $emoji
 * @property string $emojiU
 * @property bool $flag
 * @property bool $is_active
 */
class Country extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id', 'name', 'iso2', 'iso3', 'numeric_code', 'phonecode',
        'capital', 'currency', 'currency_name', 'currency_symbol',
        'tld', 'native', 'region', 'subregion', 'timezones', 'translations',
        'latitude', 'longitude', 'emoji', 'emojiU', 'flag', 'is_active',
    ];

    protected $casts = [
        'translations' => 'array',
        'timezones' => 'array',
        'flag' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Scope a query to only include active Countries.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', 1);
    }

    /**
     * get Country By iso2 code.
     *
     * @throws \Throwable
     */
    public static function getByIso2(string $iso2)
    {
        $country = static::where('iso2', strtoupper($iso2))->first();
        throw_if(is_null($country), "{$iso2} does not exist");

        return $country;
    }

    /**
     * get Country By iso3 code.
     *
     * @throws \Throwable
     */
    public static function getByIso3(string $iso3)
    {
        $country = static::where('iso3', strtoupper($iso3))->first();
        throw_if(is_null($country), "{$iso3} does not exist");

        return $country;
    }

    /**
     * @throws \Throwable
     */
    public static function getByCode(string $code)
    {
        $code = strtoupper($code);
        $columns = \mb_strlen($code) == 3 ? 'iso3' : 'iso2';
        $country = static::where($columns, $code)->first();
        throw_if(is_null($country), "{$code} does not exist");

        return $country;
    }
}
