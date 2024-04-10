<?php declare(strict_types=1);

use Nebula\Algorithm;
use Nebula\Support\Console as ConsoleSupport;

/**
 * Prints the CLI version to the user.
 * 
 * @return void
 */
function print_cli_version() {
    ConsoleSupport::writeln(\sprintf(
        "%s | %s", PROJECT_TITLE, PROJECT_LONG_VERSION
    ), 2);
}

// Prints the CLI version to the user if it is executed via the CLI.
// Otherwise, it will exit with the failure code and an error message.
if (\php_sapi_name() === "cli") {
    print_cli_version();

} else {
    ConsoleSupport::writeln(\sprintf(
        "This script is only supported via the CLI.", PROJECT_TITLE
    ));

    exit(EXIT_FAILURE);
}

/**
 * Outputs the help usage message to the user in the CLI.
 * 
 * It will output the usage message and exit with the success code.
 * 
 * @return void
 */
function print_cli_usage() {
    ConsoleSupport::writeln(
        "usage: <path> [-h | --help]"
    , 2);

    ConsoleSupport::writeln("start propagating and suppressing files");

    ConsoleSupport::writeln(
        str_repeat(" ", INDENT_SIZE) .
        "prop, propagate   Propagates files to their destinations"
    );

    ConsoleSupport::writeln(
        str_repeat(" ", INDENT_SIZE) .
        "sup, suppress     Suppresses files from their destinations"
    , 2);

    ConsoleSupport::writeln("configure your nebula directory");

    ConsoleSupport::writeln(
        str_repeat(" ", INDENT_SIZE) .
        "config            Creates a new supernova configuration file"
    , 2);
}

// Validates the  minimum number of arguments  passed via  the CLI.  The minimum
// number of arguments is 2.
if ($argc < 2) {
    print_cli_usage();
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
    ConsoleSupport::errorln();

    ConsoleSupport::errorln(\sprintf(
        "exception: %s: %s", $exception->getFile(), $exception->getLine()
    ), 2);

    ConsoleSupport::errorln(
        str_repeat(" ", INDENT_SIZE) .
        $exception->getMessage()
    );

    exit(EXIT_FAILURE);
});

// Gets the last argument from the CLI as the mandatory argument.
$arg = $argv[$argc - 1];

// Prints the usage if the user wants help. Reads the last argument from the CLI
// and determines if it is -h or --help.
if ($arg === "-h" || $arg === "--help") {
    print_cli_usage();
    exit(EXIT_SUCCESS);
}

$algo = new Algorithm();

/** @var string $path The target path to propagate or suppress. */
$path = $argv[1];

// Determines if the user wants to create a new supernova configuration file.
if ($arg === "config") {
    $algo->createConfigurationFile($path);
}

// Determines  if the user wants to propagate or  suppress the data. By default,
// the algorithm will propagate the data.
else if (\in_array($arg ,["sup", "suppress"])) {
    $algo->consume($path, false);
    $algo->suppress();

} else if (\in_array($arg, ["prop", "propagate"])) {
    $algo->consume($path);
    $algo->propagate();
}

// Prints the usage if the user enters an invalid argument.
else {
    print_cli_usage();
    exit(EXIT_FAILURE);
}
