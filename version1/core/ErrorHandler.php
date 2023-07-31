<?php

declare(strict_types=1);

function __errorHandler(int $errNo, string $errStr, ?string $errFile = null, ?int $errLine = null): void
{
    echo 'Ошибка: ' . $errStr . PHP_EOL;
    echo 'Файл: ' . $errFile . PHP_EOL;
    echo 'Строка: ' . $errLine . PHP_EOL;

    exit(1);
}

set_error_handler("__errorHandler");


function __exception(Throwable $exception): void
{
    echo 'Ошибка: ' . $exception->getMessage() . PHP_EOL;
    echo 'Файл: ' . $exception->getFile() . PHP_EOL;
    echo 'Строка: ' . $exception->getLine() . PHP_EOL;

    exit(1);
}

set_exception_handler('__exception');
