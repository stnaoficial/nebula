<?php declare(strict_types=1);

// Defines the default exit code for successes.
if (!\defined("EXIT_SUCCESS")) {
    \define("EXIT_SUCCESS", 0);
}

// Defines the default exit code for failures.
if (!\defined("EXIT_FAILURE")) {
    \define("EXIT_FAILURE", 1);
}

// Defines the default indent size.
if (!\defined("INDENT_SIZE")) {
    \define("INDENT_SIZE", 3);
}

// The title of the project.
if (!\defined("PROJECT_TITLE")) {
    \define("PROJECT_TITLE", "Nebula");
}

// The long version of the project.
if (!\defined("PROJECT_LONG_VERSION")) {
    \define("PROJECT_LONG_VERSION", "beta-0.1.2 (2024-04-10)");
}