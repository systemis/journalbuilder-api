<?php

return [
  'defaults' => [
    'guard' => 'auth0',
    'passwords' => 'users',
  ],
  'guards' => [
    'auth0' => [
      'driver' => 'auth0',
      'provider' => 'auth0',
    ],
  ],

  'providers' => [
    'users' => [
      'driver' => 'eloquent',
      'model' => App\Models\User::class,
    ],

    'auth0' => [
      'driver' => 'auth0',
      'repository' => \Auth0\Laravel\Auth\User\Repository::class
    ],

    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
  ],
  'passwords' => [
    'users' => [
      'provider' => 'users',
      'table' => 'password_resets',
      'expire' => 60,
      'throttle' => 60,
    ],
  ],
  'password_timeout' => 10800,

];
