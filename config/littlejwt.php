<?php

return [
    'key' => [
        /**
         * The type of key to sign/validate JWTs.
         * Options: secret, file, or none.
         */
        'default' => 'secret',
        /**
         * Configuration options for a secret key.
         */
        'secret' => [
            'phrase' => env('LITTLEJWT_KEY_PHRASE', ''),
            /**
             * Whether to perform checks if phrase is not set or is empty.
             * It's NOT recommended to set this to true.
             */
            'allow_unsecure' => false
        ],
        /**
         * Configurations options for a file key.
         */
        'file' => [
            /**
             * The type of file.
             * Options: pem, p12, or crt.
             */
            'type' => 'pem',
            /**
             * Path to the key file.
             */
            'path' => '/path/to/my/key/file.pem',
            /**
             * The secret to use if the file is encrypted.
             * This does not apply to crt files.
             * Leave empty if file is not encrypted.
             */
            'secret' => ''
        ],
    ],
    /**
     * The algorithm used by Little JWT.
     */
    'algorithm' => \Jose\Component\Signature\Algorithm\HS256::class,

    'builder' => [
        /**
         * Mutators to use for claims in the header and payload.
         */
        'mutators' => [
            'header' => [],
            'payload' => [
                'iat' => 'timestamp',
                'nbf' => 'timestamp',
                'exp' => 'timestamp'
            ]
        ],
        /**
         * Indicates which claims should be added to the header or payload.
         */
        'claims' => [
            'header' => [
                'alg',
                'cty',
                'typ',
                'crit'
            ],
            'payload' => [

            ]
        ]
    ],
    'builders' => [
        'default' => [
            /**
             * Buildable instance to use for this builder.
             */
            'buildable' => \LittleApps\LittleJWT\Build\Builders\DefaultBuilder::class,

            /**
             * Value to use for the 'alg' claim.
             * If null, the name of algorithm class specified in the littlejwt.algorithm key is used.
             */
            'alg' => null,

            /**
             * Number of seconds before JWT expires.
             */
            'ttl' => 3600,

            /**
             * Value to use for the 'iss' claim.
             */
            'iss' => env('APP_URL', 'http://localhost'),

            /**
             * Value to user for the 'aud' claim.
             */
            'aud' => env('APP_NAME', 'Laravel'),
        ]
    ],
    'validators' => [
        'default' => [
            /**
             * Claim keys required in the header and payload.
             */
            'required' => [
                'header' => ['alg'],
                'payload' => ['iss', 'iat', 'exp', 'nbf']
            ],

            /**
             * Number of seconds to allow after JWT expiry date/time.
             */
            'leeway' => 0,

            /**
             * Expected value for the 'alg' claim.
             * If null, the name of algorithm class specified in the littlejwt.algorithm key is used.
             */
            'alg' => null,

            /**
             * Expected value for the 'iss' claim.
             */
            'iss' => env('APP_URL', 'http://localhost'),

            /**
             * Expected value for the 'aud' claim.
             */
            'aud' => env('APP_NAME', 'Laravel'),
        ],
        'guard' => [
            /**
             * Validatable instance to use for this validator.
             */
            'validatable' => \LittleApps\LittleJWT\Validation\Validators\GuardValidator::class,

            /**
             * The model used for JWT authentication.
             * NOTE: Setting this to false will cause model classes in JWT to not be validated. This is NOT recommended.
             */
            'model' => \App\Models\User::class,
        ],
    ],
    /**
     * Configuration options for the LittleJWT guard.
     */
    'guard' => [
        /**
         * An adapter handles validating JWTs and is attached to the guard.
         */
        'adapters' => [
            'generic' => [
                /**
                 * The class for the adapter.
                 * This should not be changed.
                 */
                'adapter' => \LittleApps\LittleJWT\Guards\Adapters\GenericAdapter::class,
            ],
            'fingerprint' => [
                /**
                 * The class for the adapter.
                 * This should not be changed.
                 */
                'adapter' => \LittleApps\LittleJWT\Guards\Adapters\FingerprintAdapter::class,

                /**
                 * Name of the cookie to hold the fingerprint.
                 */
                'cookie' => 'fingerprint',

                /**
                 * How long the fingerprint cookie should live for (in minutes).
                 * If 0, the cookie has no expiry.
                 */
                'ttl' => 0,
            ],
        ],
    ],
    /**
     * Blacklist configuration options.
     */
    'blacklist' => [
        /**
         * Blacklist driver to use for storing blacklisted JWTs.
         */
        'driver' => 'cache',
        /**
         * Configuration options for cache driver.
         */
        'cache' => [
            /**
             * How long a JWT stays in the blacklist (in seconds).
             * Set to 0 to have JWTs blacklisted forever.
             */
            'ttl' => 0
        ],
        /**
         * Configurations options for database driver.
         */
        'database' => [
            /**
             * Table to store blacklisted JWTs.
             */
            'table' => 'jwt_blacklist',
            'columns' => [
                /**
                 * The name of the column that holds the JWT identifier.
                 */
                'identifier' => 'jwt',
                /**
                 * The name of the column that holds the expiry date/time for the JWT.
                 */
                'expiry' => 'expires_at'
            ]
        ]
    ],
];
