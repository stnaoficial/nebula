<?php declare(strict_types=1);

require_once __DIR__ . "/nebula.php";

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
    \define("INDENT_SIZE", 4);
}

/**
 * Prints the CLI banner to the user.
 * 
 * @return void
 */
function print_cli_banner() {
    echo \sprintf(
        "%s | %s" . str_repeat(\PHP_EOL, 2), Nebula::TITLE, Nebula::LONG_VERSION
    );

    echo \sprintf(
        str_repeat(" ", INDENT_SIZE) . "%s" . str_repeat(\PHP_EOL, 2),
        Nebula::DESCRIPTION
    );
}

// Prints the CLI banner to the user if it is executed via the CLI.
// Otherwise, it will exit with the failure code and an error message.
if (\php_sapi_name() === "cli") {
    print_cli_banner();

} else {
    echo \sprintf(
        "This script is only supported via the CLI." . \PHP_EOL,
        Nebula::TITLE
    );

    exit(EXIT_FAILURE);
}

/**
 * Outputs the help usage message to the user in the CLI.
 * 
 * It will output the usage message and exit with the success code.
 * 
 * @param string $filename The name of the file being executed.
 * 
 * @return void
 */
function print_cli_usage($filename) {
    echo \sprintf(
        "usage: <path> [-h | --help] [-s | --suppress] [-p | --propagate]" .
        str_repeat(\PHP_EOL, 2), $filename
    );
}

// Validates the  minimum number of arguments  passed via  the CLI.  The minimum
// number of arguments is 2.
if ($argc < 2) {
    print_cli_usage($argv[0]);
    exit(EXIT_FAILURE);
}

/**
 * Sets the default error handler.
 * 
 * This will throw an exception when an error occurs.
 * 
 * @param int    $severity The severity of the error.
 * @param string $message  The error message.
 * @param string $filename The name of the file where the error occurred.
 * @param int    $line     The line number where the error occurred.
 * 
 * @return void
 */
\set_error_handler(function ($severity, $message, $filename, $line) {
    throw new \ErrorException($message, 0, $severity, $filename, $line);
});

/**
 * Minimaizes the amount of output to the user.
 * 
 * Sets  the  default exception  handler. We will  exit with the  error  message
 * instead of throwing an exception.
 * 
 * @param \Exception $exception The exception to handle.
 * 
 * @return void
 */
\set_exception_handler(function ($exception) {
    echo \sprintf(
        \PHP_EOL . "exception: %s: %s" . str_repeat(\PHP_EOL, 2),
        $exception->getFile(), $exception->getLine()
    );

    echo str_repeat(" ", INDENT_SIZE) . $exception->getMessage() . \PHP_EOL;

    exit(EXIT_FAILURE);
});

// Gets the last argument from the CLI.
$arg = $argv[$argc - 1];

// Prints the usage if the user wants help. Reads the last argument from the CLI
// and determines if it is -h or --help.
if ($arg === "-h" || $arg === "--help") {
    print_cli_usage($argv[0]);
    exit(EXIT_SUCCESS);
}

// Determines  if the user wants to propagate or  suppress the data. By default,
// the algorithm will propagate the data.
if ($arg === "-s" || $arg === "--suppress") {
    (new Nebula($argv[1], false))->suppress();

} else if ($arg === "-p" || $arg === "--propagate") {
    (new Nebula($argv[1]))->propagate();

} else {
    print_cli_usage($argv[0]);
    exit(EXIT_FAILURE);
}
