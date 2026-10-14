<div class="filament-hidden">

![Laravel CNPJ](https://raw.githubusercontent.com/jeffersongoncalves/laravel-cnpj/main/art/jeffersongoncalves-laravel-cnpj.png)

</div>

# Laravel CNPJ

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-support-FFDD00?style=flat-square&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/jeffersongoncalves)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-cnpj.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-cnpj)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-cnpj/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-cnpj/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-cnpj/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-cnpj/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-cnpj.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-cnpj)

A simple Laravel package for querying Brazilian company registrations (CNPJ) through the [Minha Receita](https://minhareceita.org) API, with local database caching. Supports the **alphanumeric CNPJ** format introduced by Receita Federal (e.g. `12.ABC.345/01DE-35`).

## Features

- Lookup via [Minha Receita](https://minhareceita.org) (public or self-hosted instance)
- Numeric and alphanumeric CNPJ support, with check digit validation before any HTTP request
- Accepts formatted (`33.000.167/0001-01`), raw (`33000167000101`) and lowercase alphanumeric input
- Results cached in the database, with optional TTL
- Full API payload (partners, CNAEs, address, etc.) kept in the `data` column

## Installation

```bash
composer require jeffersongoncalves/laravel-cnpj
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag="cnpj-migrations"
php artisan migrate
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="cnpj-config"
```

```php
return [
    'url' => env('CNPJ_URL', 'https://minhareceita.org'),
    'timeout' => 10,
    'verify_ssl' => true,
    'cache_ttl' => null, // seconds; null = cache forever
];
```

## Usage

```php
use JeffersonGoncalves\Cnpj\Models\Cnpj;

$result = Cnpj::findByCnpj('33.000.167/0001-01');
// [
//     'cnpj' => '33000167000101',
//     'razao_social' => '...',
//     'nome_fantasia' => '...',
//     'uf' => 'SP',
//     'municipio' => '...',
//     'situacao_cadastral' => 'ATIVA',
//     'data' => [...], // full Minha Receita response
// ]

Cnpj::findByCnpj('12.ABC.345/01DE-35'); // alphanumeric CNPJ

Cnpj::checkCnpj('33000167000101'); // true when the company exists

Cnpj::findByCnpjInDatabase('33000167000101'); // local database only, never calls the API
```

When the CNPJ is invalid, not found, or the API is unavailable, an empty result is returned (same keys, empty values) and nothing is cached.

### Validation and formatting only

```php
use JeffersonGoncalves\Cnpj\Support\CnpjSupport;

CnpjSupport::isValid('12.ABC.345/01DE-35');   // true
CnpjSupport::sanitize('12.abc.345/01de-35');  // '12ABC34501DE35'
CnpjSupport::format('12ABC34501DE35');        // '12.ABC.345/01DE-35'
```

## Alphanumeric CNPJ

Starting in July 2026, Receita Federal issues CNPJs whose first 12 characters may contain uppercase letters (`A-Z`), while the last 2 (check digits) remain numeric. Check digits are computed with modulo 11, valuing each character as its ASCII code minus 48 (digits keep their value, `A` = 17 … `Z` = 42). Existing numeric CNPJs remain valid.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [Minha Receita](https://github.com/cuducos/minha-receita) by Eduardo Cuducos
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
