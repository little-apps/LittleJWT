![Imgur](https://i.imgur.com/N3D0oUY.png?1)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/little-apps/littlejwt)](https://packagist.org/packages/little-apps/littlejwt)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/little-apps/littlejwt/run-tests?label=tests)](https://github.com/little-apps/littlejwt/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/little-apps/littlejwt/Check%20&%20fix%20styling?label=code%20style)](https://github.com/little-apps/littlejwt/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Coverage Status](https://coveralls.io/repos/github/little-apps/LittleJWT/badge.svg?branch=main)](https://coveralls.io/github/little-apps/LittleJWT?branch=main)
[![Total Downloads](https://img.shields.io/packagist/dt/little-apps/littlejwt.svg?style=flat-square)](https://packagist.org/packages/little-apps/littlejwt)

Generate and verify JSON Web Tokens (JWTs) simply in Laravel using Little JWT. It's an alternative to [Tymon's JWT Auth library](https://github.com/tymondesigns/jwt-auth) and is designed to be more extensible.

## Show Your Support

Little Apps relies on people like you to keep our software running. If you would like to show your support for Little Registry Cleaner, then you can [make a donation](https://www.little-apps.com/?donate) using PayPal, Payza or credit card (via Stripe). Please note that any amount helps (even just $1).

## Requirements

 * PHP v7.4 or higher
 * Laravel 7.x or 8.x

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
php artisan littlejwt:secret
```

## Usage

### Building JWTs

```php
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Build\Builder;

$token = LittleJWT::createToken(function (Builder $builder) {
    $builder
        ->abc('def', true)
        ->ghi('klm')
        ->nop('qrs', false);
});

// $token = "eyJhYmMiOiJkZWYiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJMYXJhdmVsIiwiZXhwIjoxNjQzMDg1NTEwLCJnaGkiOiJrbG0iLCJpYXQiOjE2NDMwODE5MTAsImlzcyI6Imh0dHA6Ly9sb2NhbGhvc3QiLCJqdGkiOiJkZmI1NzkyNy0yMzA5LTRjMTYtOTkyOC0zYTc4NDk2NzBlOWMiLCJuYmYiOjE2NDMwODE5MTAsIm5vcCI6InFycyJ9.ZxWbIY8bYPw8ZOjxBxxtcR0-6GztbMnEStWpvpojN4k";
```

### Validating JWTs
```php
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Validation\Validator;

$token = "eyJhYmMiOiJkZWYiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJMYXJhdmVsIiwiZXhwIjoxNjQzMDg1NTEwLCJnaGkiOiJrbG0iLCJpYXQiOjE2NDMwODE5MTAsImlzcyI6Imh0dHA6Ly9sb2NhbGhvc3QiLCJqdGkiOiJkZmI1NzkyNy0yMzA5LTRjMTYtOTkyOC0zYTc4NDk2NzBlOWMiLCJuYmYiOjE2NDMwODE5MTAsIm5vcCI6InFycyJ9.ZxWbIY8bYPw8ZOjxBxxtcR0-6GztbMnEStWpvpojN4k";

$passes = LittleJWT::validateToken($token, function (Validator $validator) {
    $validator
        ->equals('abc', 'def', true, true)
        ->equals('ghi', 'klm')
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

- [Nick H](https://github.com/little-apps)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
