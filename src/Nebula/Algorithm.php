<?php

namespace Nebula;

use Nebula\Support\Console as ConsoleSupport;
use Nebula\Support\Path as PathSupport;

final class Algorithm
{
    // }
    // { Start of configurables

    /**
     * The name of the configuration file.
     */
    private const CONFIGURATION_FILENAME = "nebula.json";

    /**
     * The regex for matching variables.
     */
    private const VARIABLE_MATCH_REGEX = "/{{(.*?)}}/s";

    /**
     * @property array $options An array of configurable options.
     */
    private $options = [
        "variableMatchRegex" => self::VARIABLE_MATCH_REGEX
    ];

    // }
    // { End of configurables

    // }
    // { Start of internals

    /**
     * The "destinator" file extension.
     */
    private const FILE_EXTENSION = "neb";

    /**
     * The regex for matching file "destinators".
     */
    private const FILENAME_MATCH_REGEX = "/\[|\]\.". self::FILE_EXTENSION . "/";

    /**
     * @property <string, string> $files All consumed files.
     */
    private $files;

    /**
     * @property <string, string> $vars All consumed variables.
     */
    private $vars = [];

    // }
    // { End of internals

    /**
     * Gets the regex for matching variables.
     * 
     * @throws \UnexpectedValueException If the regex is empty.
     * 
     * @return string Returns the regex.
     */
    private function getVariableMatchRegex() {
        if (empty($this->options["variableMatchRegex"])) {
            throw new \UnexpectedValueException(\sprintf(
                "[%s] is empty.", "variableMatchRegex"
            ));
        }

        return $this->options["variableMatchRegex"];
    }

    /**
     * Creates a new configuration file in the given target directory.
     * 
     * @param string $dirname The target directory path.
     * 
     * @throws \InvalidArgumentException If the target directory is not valid.
     * 
     * @return void
     */
    public function createConfigurationFile($dirname) {
        if (!\is_dir($dirname)) {
            throw new \InvalidArgumentException(\sprintf(
                "%s is not a valid directory.", $dirname
            ));
        }

        $filename = PathSupport::join($dirname, self::CONFIGURATION_FILENAME);

        if (\file_exists($filename)) {
            throw new \InvalidArgumentException(\sprintf(
                "Configuration file %s already exists.",
                self::CONFIGURATION_FILENAME
            ));
        }

        \file_put_contents($filename, \json_encode(
            $this->options, \JSON_PRETTY_PRINT
        ));
    }

    /**
     * Consumes a configuration file.
     * 
     * @param string $path The path to the target directory.
     * 
     * @throws \InvalidArgumentException If the target directory is not valid.
     * @throws \InvalidArgumentException If the configuration file does not exist.
     * 
     * @return void
     */
    public function consumeConfigurationFile($dirname) {
        if (!\is_dir($dirname)) {
            throw new \InvalidArgumentException(\sprintf(
                "%s is not a valid directory.", $dirname
            ));
        }

        $filename = PathSupport::join($dirname, self::CONFIGURATION_FILENAME);

        if (!\file_exists($filename)) {
            throw new \InvalidArgumentException(\sprintf(
                "Configuration file %s does not exist.",
                self::CONFIGURATION_FILENAME
            ));
        }

        $this->options = \json_decode(\file_get_contents($filename), true);
    }

    /**
     * Asks the user for a variable value.
     * 
     * @param string $name The name of the variable.
     * 
     * @throws \UnexpectedValueException  If failed  to  assign  a value to  the
     *                                    variable.
     * 
     * @return string Returns the variable value.
     */
    private function askVariable($name)
    {
        $question = \sprintf("Enter a value for variable [%s]: ", $name);

        if (!$answer = ConsoleSupport::ask($question)) {
            throw new \UnexpectedValueException(\sprintf(
                "Failed to assign a value to variable [%s].", $name
            ));
        }

        return \trim($answer);
    }

    /**
     * Asks the user for variables in the source string.
     * 
     * @param string $source The source string.
     * 
     * @return void
     */
    private function askForVariables($source)
    {
        \preg_match_all($this->getVariableMatchRegex(), $source, $matches);

        if (empty($matches)) return;

        foreach ($matches[0] as $index => $match) {
            if (isset($this->vars[$match])) continue;

            $name = $matches[1][$index];

            $this->vars[$match] = $this->askVariable($name);
        }
    }

    /**
     * Checks if the source string contains variables.
     * 
     * @param string $source The source string.
     * 
     * @return bool Returns whether the source string contains variables.
     */
    private function containsVariables($source)
    {
        $matches = \preg_match_all($this->getVariableMatchRegex(), $source);

        return $matches !== false && $matches > 0;
    }

    /**
     * Consumes the given file path.
     * 
     * @param string $filename The path to the file to consume.
     * @param bool   $deep     Whether to deeply fetch variables.
     * 
     * @throws \UnexpectedValueException If failed to parse the destination name.
     * @throws \InvalidArgumentException If file is empty.
     * @throws \InvalidArgumentException If failed to read the file.
     * 
     * @return void
     */
    public function consumeFile($filename, $deep = true)
    {
        if (\pathinfo($filename, \PATHINFO_EXTENSION) !== self::FILE_EXTENSION) {
            return;
        }

        $destname = \preg_replace(
            self::FILENAME_MATCH_REGEX, "", \basename($filename)
        );

        $destname = \trim(\str_replace("\\", \DIRECTORY_SEPARATOR, $destname));

        if (empty($destname)) {
            throw new \UnexpectedValueException(\sprintf(
                "Failed to parse destination name of file %s.", $filename
            ));
        }

        $this->askForVariables($destname);

        if (\filesize($filename) == 0) {
            throw new \UnexpectedValueException(\sprintf(
                "File %s is empty.", $filename
            ));
        }
        
        if (!$contents = \file_get_contents($filename)) {
            throw new \UnexpectedValueException(\sprintf(
                "Failed to read file %s.", $filename
            ));
        }

        if ($deep) $this->askForVariables($contents);

        $this->files[$destname] = $contents;
    }

    /**
     * Consumes the given directory path.
     * 
     * @param string $dirname The path to the directory to consume.
     * @param bool   $deep    Whether to deeply fetch variables.
     * 
     * @throws \UnexpectedValueException  If an error occurs when interpretating
     *                                    the directory.
     * 
     * @return void
     */
    public function consumeDirectory($dirname, $deep = true)
    {
        $this->consumeConfigurationFile($dirname);

        foreach (\scandir($dirname) ?: [] as $filename) {
            if ($filename === "." || $filename === "..") continue;

            $this->consumeFile(
                $dirname . \DIRECTORY_SEPARATOR . $filename, $deep
            );
        }
    }

    /**
     * Consumes the given path.
     * 
     * @param string $path The path to consume.
     * @param bool   $deep Whether to deeply fetch variables.
     * 
     * @throws \UnexpectedValueException If path is not of the correct type.
     * 
     * @return void
     */
    public function consume($path, $deep = true)
    {
        if (\is_dir($path)) {
            $this->consumeDirectory($path, $deep);

        } else if (\file_exists($path)) {
            $this->consumeFile($path, $deep);

        } else {
            throw new \InvalidArgumentException(\sprintf(
                "Only [.%s] files and directories are supported.",
                self::FILE_EXTENSION
            ));
        }
    }

    /**
     * Suppresses files from its destinations.
     * 
     * @return void
     */
    public function suppress()
    {
        $suppressed = [];

        ConsoleSupport::writeln();

        foreach (\array_keys($this->files) as $destname) {
            foreach ($this->vars as $match => $value) {
                if (!\str_contains($destname, $match)) continue;

                $destname = \str_replace($match, $value, $destname);                    

                if (\file_exists($destname)) {
                    ConsoleSupport::writeln(\sprintf(
                        "Suppressing %s", $destname
                    ));

                    $suppressed[$destname] = true;

                    \unlink($destname);
                }
            }
        }

        ConsoleSupport::writeln();

        ConsoleSupport::writeln(\sprintf(
            "%s/%s files suppressed.", \count($suppressed), \count($this->files)
        ));
    }

    /**
     * Propagates files to its destinations.
     * 
     * @return void
     */
    public function propagate()
    {
        $propagated = [];

        ConsoleSupport::writeln();

        foreach ($this->files as $destname => $contents) {
            foreach ($this->vars as $match => $value) {
                $replace_destname = \str_contains($destname, $match);
                $replace_contents = \str_contains($contents, $match);

                if (!$replace_destname && !$replace_contents) continue;

                if ($replace_destname) {
                    $destname = \str_replace($match, $value, $destname);
                }

                if ($replace_contents) {
                    $contents = \str_replace($match, $value, $contents);
                }

                // Skip non-replaced file destinations.
                if ($this->containsVariables($destname)) continue;

                $dirname = \dirname($destname);

                if (!\is_dir($dirname)) \mkdir($dirname, recursive: true);

                if (!isset($propagated[$destname])) {
                    ConsoleSupport::writeln(\sprintf(
                        "Propagating %s", $destname
                    ));

                    $propagated[$destname] = true;
                }

                \file_put_contents($destname, $contents);
            }
        }

        ConsoleSupport::writeln();

        ConsoleSupport::writeln(\sprintf(
            "%s/%s files propagated.", \count($propagated), \count($this->files)
        ));
    }
}
