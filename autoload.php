<?php declare(strict_types=1);

/** @ignore Register an simple autoloader for the library. */
\spl_autoload_register(function ($class) {
    $paths = [
        __DIR__, "src",
        \str_replace("\\", \DIRECTORY_SEPARATOR, $class) . ".php"
    ];

    $filename = \implode(\DIRECTORY_SEPARATOR, \array_filter($paths, "trim"));

    if (!\file_exists($filename)) {
        return;
    }

    require_once $filename;
});