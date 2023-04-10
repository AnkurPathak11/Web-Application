<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlsrv:Server='. env('DB_HOST') .';Encrypt=No;TrustServerCertificate=Yes;Database='. env('DB_NAME'),
    'username' => env('DB_USER'),
    'password' => env('DB_PASS'),
    'charset' => 'utf8',
];
 