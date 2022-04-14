# Changelog

All notable changes to `LittleJWT` will be documented in this file.

## v1.2.0 - 2022-04-14

## What's Changed

- Configuration file changes:
- - The `littlejwt.algorithm` setting is moved to `littlejwt.key.algorithm`.
- - Settings for JWK file types are pulled from the LITTLEJWT_KEY_FILE_* environment variables by default.
- - Configuration settings (like the 'openssl.cnf' file location) for openssl functions can be set at `littlejwt.openssl`.
- 
- Generate private and PKCS12 key types with Artisan commands.
- Use random one-time JSON Web Keys.
- Centralized building `ClaimManager` instances and mutating claims with `ClaimManagerBuilder` factory.
- Supports both SignatureAlgorithm and MacAlgorithm types for JSON Web Keys.
- Tested to work with private and PKCS12 key types.
- Jose libraries are no longer provided using the Laravel application container.
- Fixed claims from not being mutated correctly.
- Fixed bug causing Fake LittleJWT instances to not be created with passed JWK.

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.1.2...v1.2.0

## v1.1.2 - 2022-03-28

## What's Changed

- The `validateToken` method is faked, allowing for tokens to also be tested.
- Various fixes to testing and documentation.

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.1.1...v1.1.2

## v1.1.1 - 2022-03-26

## What's Changed

- Fixed bug causing custom adapters to not be created.

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.1.0...v1.1.1

## v1.1.0 - 2022-03-01

- Works with PHP 7.4+ and 8.0+
- Works with Laravel 7.x, 8.x, and 9.x
- Added [Laravel validation rules](https://docs.getlittlejwt.com/en/validator-rules)
- Uses Coveralls for tracking code coverage from PHPUnit tests

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.0.1...v1.1.0

## v1.0.1 - 2022-02-15

Works with Laravel 8.x and 9.x.

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.0.0...v1.0.1

## v1.0.0 - 2022-02-13

Initial release

**Full Changelog**: https://github.com/little-apps/LittleJWT/commits/v1.0.0
