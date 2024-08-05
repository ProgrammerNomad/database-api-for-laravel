<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = '/var/www/vhosts/mobrilz.digital/data.mobrilz.digital/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require '/var/www/vhosts/mobrilz.digital/data.mobrilz.digital/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once '/var/www/vhosts/mobrilz.digital/data.mobrilz.digital/bootstrap/app.php')
    ->handleRequest(Request::capture());