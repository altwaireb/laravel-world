<?php

use Altwaireb\World\World;

beforeEach(function () {
    config()->set('world.countries.activation.only.iso2', []);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);
    config()->set('world.insert_activations_only', false);
});

it('reads country activation settings dynamically', function () {
    config()->set('world.countries.activation.only.iso2', ['SA']);

    expect(World::getIso2Activate())->toBe(['SA']);

    config()->set('world.countries.activation.only.iso2', ['US', 'GB']);

    expect(World::getIso2Activate())->toBe(['US', 'GB']);
});

it('reads country exclusion settings dynamically', function () {
    config()->set('world.countries.activation.except.iso2', ['SA']);

    expect(World::getIso2Except())->toBe(['SA']);

    config()->set('world.countries.activation.except.iso2', ['US']);

    expect(World::getIso2Except())->toBe(['US']);
});

it('detects activation-only mode dynamically', function () {
    config()->set('world.insert_activations_only', false);

    expect(World::IsInsertActivationsOnly())->toBeFalse();

    config()->set('world.insert_activations_only', true);

    expect(World::IsInsertActivationsOnly())->toBeTrue();
});

it('recalculates active country IDs when activation settings change', function () {
    config()->set('world.countries.activation.only.iso2', ['AF']);
    config()->set('world.countries.activation.only.iso3', []);

    expect(World::getIdsCountriesActiveByIso2())->toContain(1);

    config()->set('world.countries.activation.only.iso2', ['US']);

    $activeIds = World::getIdsCountriesActiveByIso2();

    expect($activeIds)->not->toContain(1);
});

it('refreshes cached active country IDs when activation settings change', function () {
    config()->set('world.countries.activation.only.iso2', ['AF']);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    expect(World::isCityActiveByCountryId(1))->toBeTrue();

    config()->set('world.countries.activation.only.iso2', ['US']);

    expect(World::isCityActiveByCountryId(1))->toBeFalse();
});

it('refreshes cached excluded country IDs when exclusion settings change', function () {
    config()->set('world.countries.activation.only.iso2', []);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', ['AF']);
    config()->set('world.countries.activation.except.iso3', []);

    expect(World::isCityActiveByCountryId(1))->toBeFalse();

    config()->set('world.countries.activation.except.iso2', ['US']);

    expect(World::isCityActiveByCountryId(1))->toBeTrue();
});
