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

    public function run(): void
    {
        try {
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
        $countries = $this->serves->getCountries();
        $chunkLength = $this->serves->getCountriesChunkLength();

        $this->command->info('Starting Seed Country Data ...');
        $this->command->getOutput()->progressStart(count($countries));

        foreach (array_chunk($countries, $chunkLength) as $chunk) {
            foreach ($chunk as $country) {
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
            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Country Data Seeded has successful');
        $this->command->line('');
    }

    protected function createStates(): void
    {
        $states = $this->serves->getStates();
        $chunkLength = $this->serves->getStatesChunkLength();

        $this->command->info('Starting Seed State Data ...');
        $this->command->getOutput()->progressStart(count($states));

        foreach (array_chunk($states, $chunkLength) as $chunk) {
            foreach ($chunk as $state) {
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
                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('State Data Seeded has successful');
        $this->command->line('');
    }

    protected function createCities(): void
    {
        $cities = $this->serves->getCities();
        $chunkLength = $this->serves->getCitiesChunkLength();

        $this->command->info('Starting Seed City Data ...');
        $this->command->getOutput()->progressStart(count($cities));

        foreach (array_chunk($cities, $chunkLength) as $chunk) {
            foreach ($chunk as $city) {
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

                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('City Data Seeded has successful');

    }
}
