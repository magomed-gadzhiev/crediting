<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=postgres;port=5432;dbname=loans',
    'username' => 'user',
    'password' => 'password',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 600,
    'schemaCache' => 'cache',
];
