![Imgur](https://i.imgur.com/N3D0oUY.png?1)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/little-apps/littlejwt)](https://packagist.org/packages/little-apps/littlejwt)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/little-apps/littlejwt/run-tests.yml?branch=main)](https://github.com/little-apps/littlejwt/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Format (PHP)](https://github.com/little-apps/LittleJWT/actions/workflows/laravel-pint.yml/badge.svg)](https://github.com/little-apps/LittleJWT/actions/workflows/laravel-pint.yml)
[![Coverage Status](https://coveralls.io/repos/github/little-apps/LittleJWT/badge.svg?branch=main)](https://coveralls.io/github/little-apps/LittleJWT?branch=main)
[![Total Downloads](https://img.shields.io/packagist/dt/little-apps/littlejwt.svg?style=flat-square)](https://packagist.org/packages/little-apps/littlejwt)

Secure Your Laravel Web App with Little JWT - The Key to Effortless Token Management!

## Show Your Support

Little Apps relies on people like you to keep our software running. If you would like to show your support for Little Registry Cleaner, then you can [make a donation](https://www.little-apps.com/?donate) using PayPal, Payza or credit card (via Stripe). Please note that any amount helps (even just $1).

## Requirements

 * PHP v7.4 or higher
 * Laravel 7.x, 8.x, 9.x, or 10.x

## Installation

Install the package via composer:

```bash
composer require little-apps/littlejwt
```

Publish the config file with:

```bash
php artisan vendor:publish --tag="littlejwt-config"
```

Generate a secret phrase for building and validating JWTs:

```bash
php artisan littlejwt:phrase
```

> Information on generating different types of keys can be found in [the documentation](https://docs.getlittlejwt.com/json-web-keys#key-types).

## Upgrading

**IMPORTANT:** Before continuing, please note v2.0 is still in beta and is not recommended for production systems.

Create a backup of the config file:

```bash
cp config/littlejwt.php config/littlejwt.php.old
```

Upgrade the package via composer:

```bash
composer require little-apps/littlejwt:"^2.0.0@beta"
```

Publish the new config file (overwriting the existing config file):

```bash
php artisan vendor:publish --tag="littlejwt-config" --existing
```

 > You will need to manually set the config file to match the old config file.

## Usage

### Building JWTs

```php
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Build\Builder;

$jwt = LittleJWT::create(function (Builder $builder) {
    $builder
        // Adds claim 'abc' with value 'def' to header claims.
        ->abc('def', true)
        // Adds claim 'ghi' with value 'klm' to payload claims.
        ->ghi('klm')
        // Adds claim 'nop' with value 'qrs' to payload claims.
        ->nop('qrs', false);
});

$token = (string) $jwt;
// $token = "ey...";
```

### Validating JWTs
```php
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Validation\Validator;

$token = "ey...";

$passes = LittleJWT::validateToken($token, function (Validator $validator) {
    $validator
        // Checks the value of the 'abc' claim in the header === (strictly equals) 'def'
        ->equals('abc', 'def', true, true)
        // Checks the value of the 'ghi' claim in the payload == (equals) 'klm'
        ->equals('ghi', 'klm')
        // Checks the value of the 'nop' claim in the payload === (strictly equals) 'qrs'
        ->equals('nop', 'qrs', true, false);
});

if ($passes) {
    // JWT is valid.
} else {
    // JWT is invalid.
}
```

## Further Documentation

Further documentation is located at [docs.getlittlejwt.com](https://docs.getlittlejwt.com/).

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

### Libraries

Little JWT is built using the following libraries:

 * [Laravel](https://laravel.com/)
 * [Laravel Package Skeleton](https://github.com/spatie/package-skeleton-laravel)
 * [PHP JWT Framework](https://github.com/web-token/jwt-framework)

### Contributors

Thank you to the following for their contributions:

- [Little Apps](https://github.com/little-apps)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
