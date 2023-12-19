<?php

/**
 * @file
 * Code for DB and other config on Upsum
 */

$driver = "mysql";
$databases['default']['default']['database'] = getenv(DB_DATABASE);
$databases['default']['default']['username'] = getenv(DB_USERNAME);
$databases['default']['default']['password'] = getenv(DB_PASSWORD);
$databases['default']['default']['host'] = getenv(DB_HOST);
$databases['default']['default']['port'] = getenv(DB_PORT);
$databases['default']['default']['driver'] = $driver;