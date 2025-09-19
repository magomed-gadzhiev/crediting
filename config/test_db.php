<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'pgsql:host=postgres;port=5432;dbname=loans';
$db['username'] = 'user';
$db['password'] = 'password';

return $db;
