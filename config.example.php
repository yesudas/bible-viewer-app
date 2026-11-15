<?php
// Copy to config.php (gitignored). Never enable test_mode on a public server.
return [
    // Gmail / Google accounts allowed into a.php. someone@gmail.com is always an admin;
    // list any additional admins here. Anyone else who signs in gets "no access".
    'admins' => [],
    // Testing mode: only honoured when the request host is localhost / 127.0.0.1.
    // Pretends the user below is already signed in with Google.
    'test_mode' => false,
    'test_user' => [
        'sub'     => 'test-user',
        'name'    => 'Some User Name',
        'email'   => 'someone@gmail.com',
        'picture' => '',
    ],
];
