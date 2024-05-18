# Changelog

All notable changes to LittleJWT will be documented in this file.

## v2.1.1-beta - 2024-05-17

## What's Changed
* The `with()` method in `ExtendedValidator` allows additional validatables to be included.
* The `without()` method in `ExtendedValidator` allows additional validatables to be excluded.
* Tests the `with()` and `without()` methods.
* Migrated PHPUnit configuration file to newer version.
* Updated package versions for GitHub actions.

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v2.1.0-beta...v2.1.1-beta

## v2.1.0-beta - 2024-05-11

## What's Changed

### High Level
 * The way additional buildables and validatables are included has changed.
 * Added support for Laravel 11 and PHP 8.2.
 * Laravel 9 (and lower) and PHP 7 is no longer supported.
 * Improved JSON Web Key secret generation.
 * Improved PHPDoc types
 * Upgraded [PHP JWT Library](https://github.com/web-token/jwt-framework) to v3.3.

### Low Level

#### Building and Validating JWTs
 * The create() and validate() methods no longer accept the `$applyDefault` parameter.
 * Additional buildables and validatables are specified in the callback function.
 * The old `Builder` class was renamed to `Options`.
 * The new `Builder` class extends `Options` and determines which buildables to use.
 * The `ExtendedValidator` extends `Validator` and determines which validatables to use.
 * There's a mutable and immutable claim manager.
 * Claims are stored as `ClaimBuildOption` instances in claim manager.
 * Replaced getHeaders() and getPayload() methods in `Builder` with getClaimManagers() method.
 * Pulls reserved header and payload claim keys directly from configuration.

#### Commands
 * The `--key` option allows the environment key to use for the `littlejwt:phrase` command.
 * The `--yes` option to skip any confirmations from `littlejwt:phrase` command.
 * Checks the .env file is writable before modifying it.

#### Miscellaneous 
 * Replaced PHP CS Fixer with Laravel Pint to cleanup code styling.

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v2.0.1-beta...v2.1.0-beta

## v2.0.1-beta - 2023-05-20

## What's Changed

 * Fixed issue when 'alg' is not set in config file.
 * Added upgrading instructions to README file.

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v2.0.0-beta...v2.0.1-beta

## v2.0.0-beta - 2023-05-20

## What's Changed

### High Level
 * Major updates to functionality and design.
 * Implemented claim mutating (serializing and unserializing).
 * Various fixes and updates to both the code and documentation.
 * The [LittleJWT documentation](https://docs.getlittlejwt.com) has been updated to reflect the changes.

### Low Level
 * The `createJWT` method has been renamed to `create`.
 * The `parseToken` method has been renamed to `parse`.
 * The `validateJWT` method has been renamed to `validate`.
 * Removed the `createToken` method.
 * The `validate` method returns an `ValidatedJsonWebToken` object, not a boolean.
 * The `LittleApps\LittleJWT\JWT\JWT` class has been renamed to `JsonWebToken`.
 * The `LittleApps\LittleJWT\JWK\JsonWebKey` class extends `Jose\Component\Core\JWK`.
 * The `createUnsigned` method always creates an unsigned JWT.
 * The `createSigned` method always creates and signs a JWT.
 * The `create` method creates and signs a JWT depending if auto sign is enabled.
 * LittleJWT forwards calls to the mutate/non-mutate handler.
 * Creating, parsing, signing, validating, etc. are in separate traits.
 * Added option to enable/disable auto signing JWTs.

## v1.5.1 - 2023-04-17

### What's Changed

- Removed unneeded call to buildValidator in Valid constructor.
- Uses [ATOM constant in DateTimeInterface](https://www.php.net/DateTimeInterface) to format date/time in ISO8601.
- Fixed tests for base64 URL encoding and decoding.
- Bump dependabot/fetch-metadata from 1.3.5 to 1.3.6 by **@dependabot** in [#19](https://github.com/little-apps/LittleJWT/pull/19)

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.5.0...v1.5.1

## v1.5.0 - 2023-02-26

### What's Changed

- Supports Laravel 10.x and PHP 8.1.
- Removed web-token/jwt-easy package dependency.
- The ``LittleApps\LittleJWT\Exceptions\InvalidClaimValueException`` is thrown if a JWT claim cannot be encoded.
- Base64 URL encoding and decoding is done internally.

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.4.0...v1.5.0

## v1.4.0 - 2022-08-21

### What's Changed

- Added `littlejwt:purge` command to purge blacklist.
- Create tests for the blacklist.
- Bump dependabot/fetch-metadata from 1.3.1 to 1.3.3 by @dependabot in https://github.com/little-apps/LittleJWT/pull/14

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.3.0...v1.4.0

## v1.3.0 - 2022-06-24

### What's Changed

- Renamed the "littlejwt:secret" command to "littlejwt:phrase".
- The `web-token/jwt-signature-algorithm-hmac` package is automatically installed with Little JWT (fixes issue #12).
- The `LittleApps\LittleJWT\Exceptions\InvalidHashAlgorithmException` exception is thrown if no hashing algorithm is set in the config file.
- Uses `LittleApps\LittleJWT\Concerns\PassableThru` trait to send `LittleApps\LittleJWT\Build\Builder` and `LittleApps\LittleJWT\Validation\Validator` instances through callbacks.

#### Notes

- This is considered a minor version update (and not a patch to version 1.2) because it now automatically installs a Composer package and it may cause issues updating Composer (possibly because a different version or variation of the `web-token/jwt-signature-algorithm-hmac` package was installed).

**Full Changelog**: https://github.com/little-apps/LittleJWT/compare/v1.2.0...v1.2.1

## v1.2.0 - 2022-04-14

## What's Changed

- Configuration file changes:
  - The `littlejwt.algorithm` setting is moved to `littlejwt.key.algorithm`.
  - Settings for JWK file types are pulled from the LITTLEJWT_KEY_FILE_* environment variables by default.
  - Configuration settings (like the 'openssl.cnf' file location) for openssl functions can be set at `littlejwt.openssl`.
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
