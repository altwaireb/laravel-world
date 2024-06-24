<?php

namespace Altwaireb\World;

use Altwaireb\World\Exceptions\InvalidCodeException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class World
{
    public static function isActivateCountries(): bool
    {
        return Config::get('world.countries.activation.default', true);
    }

    public static function isActivateStates(): bool
    {
        return Config::get('world.states.activation.default', true);
    }

    public static function isActivateCities(): bool
    {
        return Config::get('world.cities.activation.default', true);
    }

    public static function getIso2Activate(): array
    {
        return Config::get('world.countries.activation.only.iso2', []);
    }

    public static function getIso3Activate(): array
    {
        return Config::get('world.countries.activation.only.iso3', []);
    }

    public static function getIso2Except(): array
    {
        return Config::get('world.countries.activation.except.iso2', []);
    }

    public static function getIso3Except(): array
    {
        return Config::get('world.countries.activation.except.iso3', []);
    }

    public static function getCountriesChunkLength(): int
    {
        return Config::get('world.countries.chunk_length', 50);
    }

    public static function getStatesChunkLength(): int
    {
        return Config::get('world.states.chunk_length', 200);
    }

    public static function getCitiesChunkLength(): int
    {
        return Config::get('world.cities.chunk_length', 200);
    }

    // Activate
    public static function hasIso2Activate(): bool
    {
        return ! empty(self::getIso2Activate());
    }

    public static function hasIso3Activate(): bool
    {
        return ! empty(self::getIso3Activate());
    }

    public static function hasIsoCodeActivate(): bool
    {
        return self::hasIso2Activate() || self::hasIso3Activate();
    }

    // Except
    public static function hasIso2Except(): bool
    {
        return ! empty(self::getIso2Except());
    }

    public static function hasIso3Except(): bool
    {
        return ! empty(self::getIso3Except());
    }

    public static function hasIsoCodeExcept(): bool
    {
        return self::hasIso2Except() || self::hasIso3Except();
    }

    // Tables

    public static function hasAllTables(): bool
    {
        return self::hasCountriesTable() &&
            self::hasStatesTable() &&
            self::hasCitiesTable();
    }

    public static function hasCountriesTable(): bool
    {
        return self::hasTable(tableName: 'countries');
    }

    public static function hasStatesTable(): bool
    {
        return self::hasTable(tableName: 'states');
    }

    public static function hasCitiesTable(): bool
    {
        return self::hasTable(tableName: 'cities');
    }

    public static function isAllTablesEmpty(): bool
    {
        return self::isCitiesTableEmpty() === true &&
            self::isStatesTableEmpty() === true &&
            self::isCitiesTableEmpty() === true;
    }

    public static function isCountriesTableEmpty(): bool
    {
        return self::isTableEmpty(tableName: 'countries');
    }

    public static function isStatesTableEmpty(): bool
    {
        return self::isTableEmpty(tableName: 'states');
    }

    public static function isCitiesTableEmpty(): bool
    {
        return self::isTableEmpty(tableName: 'cities');
    }

    private static function hasTable(string $tableName): bool
    {
        return Schema::hasTable($tableName);
    }

    private static function isTableEmpty($tableName): bool
    {
        if (! self::hasTable($tableName) && (DB::table($tableName)->count() > 0)) {
            return false;
        }

        return true;
    }

    public static function isSeedersPublished(): bool
    {
        return ! empty(glob(database_path('seeders/WorldTableSeeder.php')));
    }

    // get Ids
    public static function getIdsCountriesActiveByIso2(): array
    {
        if (! self::hasIso2Activate()) {
            return [];
        }

        return self::getCountriesIdsBy(
            column: 'iso2',
            values: self::getIso2Activate()
        );
    }

    public static function getIdsCountriesActiveByIso3(): array
    {
        if (! self::hasIso3Activate()) {
            return [];
        }

        return self::getCountriesIdsBy(
            column: 'iso3',
            values: self::getIso3Activate()
        );
    }

    public static function getIdsCountriesExceptByIso2(): array
    {
        if (! self::hasIso2Except()) {
            return [];
        }

        return self::getCountriesIdsBy(
            column: 'iso2',
            values: self::getIso2Except()
        );
    }

    public static function getIdsCountriesExceptByIso3(): array
    {
        if (! self::hasIso3Except()) {
            return [];
        }

        return self::getCountriesIdsBy(
            column: 'iso3',
            values: self::getIso3Except()
        );
    }

    private static function getCountriesIdsBy(string $column, array $values): array
    {
        $countries = collect(self::getCountries());

        return $countries->whereIn($column, $values)->pluck('id')->toArray();
    }

    public static function getCountries(): array
    {
        return self::getJsonFileAsArray('countries');
    }

    public static function getStates(): array
    {
        return self::getJsonFileAsArray('states');
    }

    public static function getCities(): array
    {
        return self::getJsonFileAsArray('cities');
    }

    private static function getJsonFileAsArray(string $fileName)
    {
        $data = File::get(__DIR__."/../database/data/$fileName.json");
        if (! $data) {
            return [];
        }

        return json_decode($data);
    }

    protected static function isIso2CountriesActive(string $iso2): bool
    {
        if (! self::hasIso2Activate()) {
            return false;
        }

        return in_array($iso2, self::getIso2Activate());
    }

    protected static function isIso3CountriesActive(string $iso3): bool
    {
        if (! self::hasIso3Activate()) {
            return false;
        }

        return in_array($iso3, self::getIso3Activate());
    }

    protected static function isIso2CountriesExcept(string $iso2): bool
    {
        if (! self::hasIso2Except()) {
            return false;
        }

        return in_array($iso2, self::getIso2Except());
    }

    protected static function isIso3CountriesExcept(string $iso3): bool
    {
        if (! self::hasIso3Except()) {
            return false;
        }

        return in_array($iso3, self::getIso3Except());
    }

    // check if Country Active By Iso2 Or Iso3 in config file
    public static function isCountryActiveByIso2OrIso3(string $iso2, string $iso3): bool
    {
        if (self::hasIsoCodeExcept()) {
            if (! empty($iso2) && self::hasIso2Except()) {
                if (self::isIso2CountriesExcept($iso2)) {
                    return false;
                }
            }
            if (! empty($iso3) && self::hasIso3Except()) {
                if (self::isIso3CountriesExcept($iso3)) {
                    return false;
                }
            }
        }

        if (self::hasIsoCodeActivate()) {
            if (! empty($iso2) && self::hasIso2Activate()) {
                if (self::isIso2CountriesActive($iso2)) {
                    return self::isIso2CountriesActive($iso2);
                }
            }
            if (! empty($iso3) && self::hasIso3Activate()) {
                if (self::isIso3CountriesActive($iso3)) {
                    return self::isIso3CountriesActive($iso3);
                }
            }

            return false;
        }

        return self::isActivateCountries();
    }

    // check if State Active By id Country

    public static function isStateActiveByCountryId(int $countryId): bool
    {
        if (self::hasIsoCodeExcept() && ! empty($countryId)) {
            if (self::isIdCountryExcept($countryId)) {
                return false;
            }
        }
        if (self::hasIsoCodeActivate() && ! empty($countryId)) {
            return self::isIdCountryActive($countryId);
        }

        return self::isActivateStates();
    }

    public static function isCityActiveByCountryId(int $countryId): bool
    {
        if (self::hasIsoCodeExcept() && ! empty($countryId)) {
            if (self::isIdCountryExcept($countryId)) {
                return false;
            }
        }

        if (self::hasIsoCodeActivate() && ! empty($countryId)) {
            return self::isIdCountryActive($countryId);
        }

        return self::isActivateCities();
    }

    private static function isIdCountryExcept(int $countryId): bool
    {
        return in_array($countryId, self::getIdsCountriesExceptByIso2OrIso3());

    }

    private static function getIdsCountriesExceptByIso2OrIso3(): array
    {
        $ids = [];

        if (self::hasIso2Except()) {
            $ids = array_merge($ids, self::getIdsCountriesExceptByIso2());
        }

        if (self::hasIso3Except()) {
            $ids = array_merge($ids, self::getIdsCountriesExceptByIso3());
        }
        if (! empty($ids)) {
            return array_unique($ids);
        }

        return $ids;
    }

    private static function isIdCountryActive(int $countryId): bool
    {
        return in_array($countryId, self::getIdsCountriesActiveByIso2OrIso3());
    }

    private static function getIdsCountriesActiveByIso2OrIso3(): array
    {
        $idsIso2 = self::getIdsCountriesActiveByIso2();
        $idsIso3 = self::getIdsCountriesActiveByIso3();
        $ids = array_merge(
            $idsIso2,
            $idsIso3
        );

        if (empty($ids)) {
            return [];
        }

        return array_unique($ids);
    }

    /**
     * @throws InvalidCodeException
     */
    public static function ensureIsoCodesIsValid(): void
    {
        self::ensureIso2IsValid();
        self::ensureIso3IsValid();

    }

    /**
     * @throws InvalidCodeException
     */
    protected static function ensureIso2IsValid(): void
    {
        if (! empty(self::getIso2ActivateOrExcept())) {
            foreach (self::getIso2ActivateOrExcept() as $key => $value) {
                if (! self::isIsoCodeValid(
                    column: 'iso2',
                    value: $value
                )) {
                    throw InvalidCodeException::iso2CodeNotFound($value);
                }
            }
        }
    }

    /**
     * @throws InvalidCodeException
     */
    protected static function ensureIso3IsValid(): void
    {
        if (! empty(self::getIso3ActivateOrExcept())) {
            foreach (self::getIso3ActivateOrExcept() as $value) {
                if (! self::isIsoCodeValid(
                    column: 'iso3',
                    value: $value
                )) {
                    throw InvalidCodeException::iso3CodeNotFound($value);
                }
            }
        }
    }

    protected static function getIso2ActivateOrExcept(): array
    {
        $iso2 = [];
        if (self::hasIso2Activate()) {
            $iso2 = array_merge($iso2, self::getIso2Activate());
        }
        if (self::hasIso2Except()) {
            $iso2 = array_merge($iso2, self::getiso2Except());
        }

        if (! empty($iso2)) {
            return array_unique($iso2);
        }

        return $iso2;
    }

    private static function isIsoCodeValid(string $column, string $value): bool
    {
        $validate = collect(self::getCountries())
            ->pluck($column);
        $validate = $validate->all();

        return in_array($value, $validate);
    }

    private static function getIso3ActivateOrExcept(): array
    {
        $iso3 = [];
        if (self::hasIso3Activate()) {
            $iso3 = array_merge($iso3, self::getIso3Activate());
        }
        if (self::hasIso3Except()) {
            $iso3 = array_merge($iso3, self::getIso3Except());
        }

        if (! empty($iso3)) {
            return array_unique($iso3);
        }

        return $iso3;
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    public static function getMigrationFileName(string $migrationFileName): ?string
    {

        $filesystem = app()->make(Filesystem::class);
        $databasePath = app()->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR;

        $fullFileName = Collection::make([$databasePath])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->first();

        if (is_null($fullFileName)) {
            return null;
        }

        return Str::afterLast($fullFileName, $databasePath);

    }

    public static function hasMigrationFileName(string $migrationFileName): bool
    {
        return ! is_null(self::getMigrationFileName($migrationFileName));
    }
}
