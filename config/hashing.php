<?php

return [
    'driver' => 'bcrypt',

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        // A stored value that is not bcrypt, such as a leftover MD5, is a
        // failed sign-in. Verifying the algorithm first would throw instead and
        // answer the request with a 500.
        'verify' => false,
        'limit' => null,
    ],

    'rehash_on_login' => true,
];
