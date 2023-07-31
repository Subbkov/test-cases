<?php

declare(strict_types=1);

require 'Config.php';
require 'ErrorHandler.php';
require 'Vendor.php';
require 'db/DB.php';

if (PHP_VERSION_ID < 70400) {
    throw new Exception('Обновите версию PHP. Минимальная версия 7.4');
}

require 'CoreFunctions.php';
