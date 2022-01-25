<?php

return [
    'key' => [
        /**
         * The mechanism to sign/validate JWTs.
         * Options: secret, file, or none.
         */
        'default' => 'secret',
        'secret' => [
            'phrase' => env('LITTLEJWT_KEY_PHRASE', ''),
            /**
             * Whether to perform checks if phrase is not set or is empty.
             * It's NOT recommended to set this to true.
             */
            'allow_unsecure' => false
        ],
        'file' => [
            /**
             * The type of file.
             * Options: pem, p12, or crt.
             */
            'type' => 'pem',
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
    'blacklist' => [
        /**
         * Blacklist driver to use for storing blacklisted JWTs.
         */
        'driver' => 'cache',
        'cache' => [
            /**
             * How long a JWT stays in the blacklist (in seconds).
             * Set to 0 to have JWTs blacklisted forever.
             */
            'ttl' => 0
        ],
        'database' => [
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
    /**
     * Claim options to use for the default generated JWTs.
     */
    'claims' => [
        'alg' => 'HS256',
        'ttl' => 3600, // Number of seconds before JWT expiry.
        'leeway' => 0,
        'iss' => env('APP_URL', 'http://localhost'),
        'aud' => env('APP_NAME', 'Laravel'),
        'required' => [
            'header' => ['alg'],
            'payload' => ['iss', 'iat', 'exp', 'nbf']
        ],
        /**
         * Claims that are timestamps and need to be serialized.
         */
        'timestamps' => ['iat', 'nbf', 'exp']
    ]
];
