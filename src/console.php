<?php

final class Console
{
    /**
     * Outputs an error message to the user.
     * 
     * @param string $message The message to output.
     * 
     * @return void
     */
    public static function error($message)
    {
        \fwrite(\STDERR, $message);
    }

    /**
     * Outputs a message to the user.
     * 
     * @param string $message The message to output.
     * 
     * @return void
     */
    public static function write($message)
    {
        \fwrite(\STDOUT, $message);
    }

    /**
     * Outputs a message to the user with a new line.
     * 
     * @param string $message The message to output.
     * @param int    $break   The number of new lines to add.
     * 
     * @return void
     */
    public static function writeln($message = "", $break = 1)
    {
        self::write($message . str_repeat(\PHP_EOL, $break));
    }

    /**
     * Reads a line from the user.
     * 
     * @return string
     */
    public static function read()
    {
        return \fgets(\STDIN);
    }

    /**
     * Asks the user for input.
     * 
     * @param string $question The question to ask.
     * 
     * @return string
     */
    public static function ask($question)
    {
        self::write($question);
        return self::read();
    }
}