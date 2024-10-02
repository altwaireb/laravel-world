<?php

namespace Altwaireb\World\Database\Seeders;

use Altwaireb\World\Exceptions\InvalidCodeException;
use Altwaireb\World\Models\City;
use Altwaireb\World\Models\Country;
use Altwaireb\World\Models\State;
use Altwaireb\World\World;
use Illuminate\Database\Seeder;

class BaseWorldSeeder extends Seeder
{
    public function __construct(
        protected World $serves
    ) {}

    /**
     * @throws \Altwaireb\World\Exceptions\IsoCodesIsEmptyException
     */
    public function run(): void
    {
        try {
            $this->serves->ensureIsInsertActivationsHasIsoCodes();
            $this->serves->ensureIsoCodesIsValid();
            $this->createCountries();
            $this->createStates();
            $this->createCities();
        } catch (InvalidCodeException $e) {
            $this->command->error($e->getMessage());
        }
    }

    protected function createCountries(): void
    {
        $countries = $this->getAllCountries();
        $chunkLength = $this->serves->getCountriesChunkLength();

        $this->command->info('Starting Seed Countries Data ...');
        $this->command->getOutput()->progressStart(count($countries));

        foreach (array_chunk($countries, $chunkLength) as $chunk) {
            foreach ($chunk as $country) {
                if ($this->serves->IsInsertActivationsOnly() === true) {

                    if ($this->serves->isCountryActiveByIso2OrIso3(
                        iso2: $country->iso2,
                        iso3: $country->iso3
                    ) === true) {
                        $this->createCountry($country);
                    }
                } else {
                    $this->createCountry($country);
                }
            }
            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Countries Data Seeded has successful');
        $this->command->line('');
    }

    protected function createStates(): void
    {
        $states = $this->getAllStates();
        $chunkLength = $this->serves->getStatesChunkLength();

        $this->command->info('Starting Seed States Data ...');
        $this->command->getOutput()->progressStart(count($states));

        foreach (array_chunk($states, $chunkLength) as $chunk) {
            foreach ($chunk as $state) {
                if ($this->serves->IsInsertActivationsOnly() === true) {
                    if ($this->serves->isStateActiveByCountryId(
                        countryId: $state->country_id
                    )) {
                        $this->createState($state);
                    }
                } else {
                    $this->createState($state);
                }

                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('States Data Seeded has successful');
        $this->command->line('');

    }

    protected function createCities(): void
    {
        $cities = $this->getAllCities();
        $chunkLength = $this->serves->getCitiesChunkLength();

        $this->command->info('Starting Seed Cities Data ...');
        $this->command->getOutput()->progressStart(count($cities));

        foreach (array_chunk($cities, $chunkLength) as $chunk) {
            foreach ($chunk as $city) {
                if ($this->serves->IsInsertActivationsOnly() === true) {
                    if ($this->serves->isCityActiveByCountryId(
                        countryId: $city->country_id
                    ) === true) {
                        $this->createCity($city);

                    }
                } else {
                    $this->createCity($city);
                }

                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Cities Data Seeded has successful');

    }

    private function getAllCountries(): array
    {
        $allCountries = collect($this->serves->getCountries());
        $idsCountriesActive = $this->serves->getIdsCountriesActive();

        if ($this->serves->IsInsertActivationsOnly() === true) {
            if (! empty($idsCountriesActive)) {
                if (count($idsCountriesActive) === 1) {
                    $countries = $allCountries->where('id', $idsCountriesActive[0])->toArray();
                } elseif (count($idsCountriesActive) > 1) {
                    $countries = $allCountries->whereIn('id', $idsCountriesActive)->toArray();
                } else {
                    $countries = [];
                }

                return $countries;
            }
        }

        return $allCountries->toArray();
    }

    private function getAllStates(): array
    {
        $allStates = collect($this->serves->getStates());

        $idsCountriesActive = $this->serves->getIdsCountriesActive();

        if ($this->serves->IsInsertActivationsOnly() === true) {
            if (! empty($idsCountriesActive)) {
                if (count($idsCountriesActive) === 1) {
                    $states = $allStates->where('country_id', $idsCountriesActive[0])->toArray();
                } elseif (count($idsCountriesActive) > 1) {
                    $states = $allStates->whereIn('country_id', $idsCountriesActive)->toArray();

                } else {
                    $states = [];
                }

                return $states;
            }
        }

        return $allStates->toArray();
    }

    private function getAllCities(): array
    {
        $allCities = collect($this->serves->getCities());

        $idsCountriesActive = $this->serves->getIdsCountriesActive();

        if ($this->serves->IsInsertActivationsOnly() === true) {
            if (! empty($idsCountriesActive)) {
                if (count($idsCountriesActive) === 1) {
                    $cities = $allCities->where('country_id', $idsCountriesActive[0])->toArray();
                } elseif (count($idsCountriesActive) > 1) {
                    $cities = $allCities->whereIn('country_id', $idsCountriesActive)->toArray();
                } else {
                    $cities = [];
                }

                return $cities;
            }
        }

        return $allCities->toArray();
    }

    protected function createCountry($country): void
    {
        Country::create([
            'id' => $country->id,
            'name' => $country->name,
            'iso2' => $country->iso2,
            'iso3' => $country->iso3,
            'numeric_code' => $country->numeric_code,
            'phonecode' => $country->phonecode,
            'capital' => $country->capital,
            'currency' => $country->currency,
            'currency_name' => $country->currency_name,
            'currency_symbol' => $country->currency_symbol,
            'tld' => $country->tld,
            'native' => $country->native,
            'region' => $country->region,
            'subregion' => $country->subregion,
            'timezones' => $country->timezones,
            'translations' => $country->translations,
            'latitude' => $country->latitude,
            'longitude' => $country->longitude,
            'emoji' => $country->emoji,
            'emojiU' => $country->emojiU,
            'flag' => $country->flag,
            'is_active' => $this->serves->isCountryActiveByIso2OrIso3(
                iso2: $country->iso2,
                iso3: $country->iso3
            ),
        ]);
    }

    protected function createState($state): void
    {
        State::create([
            'id' => $state->id,
            'name' => $state->name,
            'country_id' => $state->country_id,
            'latitude' => $state->latitude,
            'longitude' => $state->longitude,
            'is_active' => $this->serves->isStateActiveByCountryId(
                countryId: $state->country_id
            ),
        ]);
    }

    protected function createCity(mixed $city): void
    {
        City::create([
            'id' => $city->id,
            'name' => $city->name,
            'country_id' => $city->country_id,
            'state_id' => $city->state_id,
            'latitude' => $city->latitude,
            'longitude' => $city->longitude,
            'is_active' => $this->serves->isCityActiveByCountryId(
                countryId: $city->country_id
            ),
        ]);
    }
}
