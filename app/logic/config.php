<?php

/**
 * Config file containing the database configuration for the application.
 *
 * Get the database configuration from the config.json file.
 */

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
define('DB_HOST', $config['HOSTNAME']);
define('DB_USER', $config['USERNAME']);
define('DB_PASS', $config['PASSWORD']);
define('DB_NAME', $config['DATABASE']);