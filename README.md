# Laravel World
This package allows you to add all Countries, States, and Cities data with DB Migration & Seeder for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/altwaireb/laravel-world.svg?style=flat-square)](https://packagist.org/packages/altwaireb/laravel-world)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/altwaireb/laravel-world/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/altwaireb/laravel-world/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/altwaireb/laravel-world/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/altwaireb/laravel-world/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/altwaireb/laravel-world.svg?style=flat-square)](https://packagist.org/packages/altwaireb/laravel-world)


## Numbers
| Model   | Number of items |
|---------|-----------------|
| Country | 250             |
| State   | 4961            |
| City    | 148059          |


## Attributes

Common attributes:

- `name`: Common name of Country(english).
- `iso2`: ISO-3166-2 code.
- `iso3`: ISO-3166-3 code.
- `numeric_code`: Country Numeric code.
- `phonecode`: Country Phone code.
- `capital`: Capital of this country.
- `currency`: ISO-4177 Currency Code, e.g. USD, CNY.
- `currency_name`: Currency Name.
- `currency_symbol`: Currency Symbol e.g. $, ¥.
- `tld`: Country code top-level domain e.g. .uk.
- `native`: Local name of the country.
- `region`: region of the country.
- `subregion`: Sub-region of the country.
- `timezones`: timezones the country.
    - `zoneName`: Zone Name e.g. America/New_York.
    - `gmtOffset`: GMT offset e.g. -18000.
    - `gmtOffsetName`: GMT offset Name e.g. UTC-05:00.
    - `abbreviation`: abbreviation e.g. EST.
    - `tzName`: time zone Name e.g. Eastern Standard Time (North America).
- `translations`: Country name translations e.g.
    - "ar": "الولايات المتحدة الأمريكية"
    - "kr": "미국"
    - "fr": "États-Unis"
- `latitude`: latitude the country.
- `longitude`: latitude the country.
- `emoji`: Emoji flag of country e.g. 🇺🇸.
- `emojiU`: Emoji Unicode flag of country e.g U+1F1FA U+1F1F8.
- `flag`: Country has flag (boolean).
- `is_active`: Country has active (boolean).


## Installation

You can install the package via composer:

```bash
composer require altwaireb/laravel-world
```

## Usage
Now run the following command to install:
```bash
php artisan world:install
```

Add the seeder file in `database\seeders\DatabaseSeeder.php` and add this line to use the `php artisan db:seed` command.
```php
public function run(): void
    {

        $this->call(WorldTableSeeder::class);
        ...
    }
```

Alternatively, you can seed data for Countries, States, and Cities by running this command.
```bash
php artisan world:seeder
```

You can also refresh to re-seed data for Countries, States, and Cities by running this command.
```bash
php artisan world:seeder --refresh
```

You can specify the activation of countries through the country code ISO2 or ISO3 before processing the seed data in the config file `config/world.php`.
```php
return [
    'insert_activations_only' => false,
    'countries' => [
        'activation' => [
            'default' => true,
            'only' => [
                'iso2' => ['SA','GB','DE'],
                'iso3' => ['USA','BRA','EGY'],
            ],
            'except' => [
                'iso2' => ['GA'],
                'iso3' => ['HTI'],
            ],
        ],
        'chunk_length' => 50,
    ],

    'states' => [
        'activation' => [
            'default' => true,
        ],
        'chunk_length' => 200,
    ],

    'cities' => [
        'activation' => [
            'default' => true,
        ],
        'chunk_length' => 200,
    ],
];
```

If you need to insert only the activated countries, this will insert only two countries (Albania and Argentina) with their States and Cities.

```php
return [
    'insert_activations_only' => true,
    'countries' => [
        'activation' => [
            'default' => true,
            'only' => [
                'iso2' => ['AL','AR'],
                'iso3' => [],
            ],
            'except' => [
                'iso2' => [],
                'iso3' => [],
            ],
        ],
        'chunk_length' => 50,
    ],

    ...
];
```


This means that only these two countries and the states and cities affiliated with them will be activated.

+ Note: If both `iso2` and `iso3` are empty, the `is_active` column takes the default value from the config file.
+ Note: If a country is active, all its states and cities are active.
+ Note: If a country is in the `except` list for `iso2` or `iso3`, the `is_active` column takes the FALSE value.
+ Note: If a country is not active, all its states and cities are not active.

## Usage

You can get a country by its ISO2 or ISO3 code, or both.
If you want to get a country by ISO2, you can use the static function `getByIso2`.

```php
use App\Models\Country;

$sa = Country::getByIso2('SA');
$sa->name; // Saudi Arabia
$sa->iso2; // SA
$sa->iso3; // SAU
$sa->currency_symbol; // ﷼
$sa->native; // المملكة العربية السعودية
```

If you want to get a country by ISO3, you can use the following.

```php
use App\Models\Country;

$bra = Country::getByIso3('BRA');
$bra->name; // Brazil
$bra->iso2; // BR
$bra->iso3; // BRA
$bra->currency_symbol; // R$
$bra->native; // Brasil
```
And if you want to get a country by either ISO2 or ISO3 code, you can use the following.

```php
use App\Models\Country;

$bra = Country::getByCode('PT');
$bra->name; // Portugal
$bra->iso2; // PT
$bra->iso3; // PRT
$bra->currency_symbol; // €
$bra->native; // Portugal
```

### Scope

You can use Scope to filter active data by using the following.

```php
use App\Models\Country;

$countries = Country::active()->pluck('name','id');
```

## Credits

- [Abdulmajeed Altwaireb](https://github.com/altwaireb)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
