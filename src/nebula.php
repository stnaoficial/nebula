<?php

final class Nebula
{
    public const TITLE         = "Nebula 🔭";
    public const DESCRIPTION   = "A CLI tool to easily suppress and propagate file descendants.";
    public const SHORT_VERSION = "0.1.0";
    public const LONG_VERSION  = "beta-0.1.0 (2024-04-07)";

    private const FILE_EXTENSION = "neb";

    /**
     * @property <string, string> $files An array of files.
     */
    private $files;

    /**
     * @property <string, string> $vars An array of variables.
     */
    private $vars = [];

    /**
     * Creates a new instance of the class.
     * 
     * @param string $path The target path.
     * @param bool   $deep Whether to deeply fetch variables.
     * 
     * @throws \InvalidArgumentException If the path is not a file or directory.
     * 
     * @return void
     */
    public function __construct($path, $deep = true)
    {
        if (\is_dir($path)) {
            $this->addDirectory($path, $deep);

        } else if (\file_exists($path)) {
            $this->addFile($path, $deep);

        } else {
            throw new \InvalidArgumentException(\sprintf(
                "Only [.%s] files and directories are supported.",
                self::FILE_EXTENSION
            ));
        }
    }

    /**
     * Asks the user for a variable value.
     * 
     * @param string $name The name of the variable.
     * 
     * @throws \UnexpectedValueException If failed to read the value.
     * 
     * @return string Returns the variable value.
     */
    private function askVariable($name)
    {
        \fwrite(\STDOUT, "Enter a value for variable [$name]: ");

        if (!$value = \fgets(\STDIN)) {
            throw new \UnexpectedValueException(
                "Failed to assign value to variable."
            );
        }

        return \trim($value);
    }

    /**
     * Fetches variables in a given source string.
     * 
     * @param string $source The source string.
     * 
     * @return void
     */
    private function fetchVariables($source)
    {
        \preg_match_all("/{{(.*?)}}/s", $source, $matches);

        if (empty($matches)) {
            return;
        }

        foreach ($matches[0] as $index => $match) {
            $name = $matches[1][$index];

            if (isset($this->vars[$match])) {
                continue;
            }

            $this->vars[$match] = $this->askVariable($name);
        }
    }

    /**
     * Adds a file to the list of files.
     * 
     * @param string $filename The path to the file.
     * @param bool   $deep     Whether to deeply fetch variables.
     * 
     * @throws \UnexpectedValueException If the file is not of the correct type.
     * @throws \InvalidArgumentException If failed to read the file.
     * 
     * @return void
     */
    private function addFile($filename, $deep = true)
    {
        if (\pathinfo($filename, \PATHINFO_EXTENSION) !== self::FILE_EXTENSION) {
            throw new \InvalidArgumentException(\sprintf(
                "Only [.%s] files are supported.", self::FILE_EXTENSION
            ));
        }

        $destname = \preg_replace(
            "/\[|\]\.". self::FILE_EXTENSION ."/",
            "", \basename($filename)
        );

        $destname = \str_replace("\\", \DIRECTORY_SEPARATOR, $destname);

        $this->fetchVariables($destname);

        if (!$contents = \file_get_contents($filename)) {
            throw new \UnexpectedValueException("Failed to read file.");
        }

        if ($deep) {    
            $this->fetchVariables($contents);
        }

        $this->files[$destname] = $contents;
    }

    /**
     * Adds a directory to the list of files.
     * 
     * @param string $dirname The path to the directory.
     * @param bool   $deep    Whether to deeply fetch variables.
     * 
     * @return void
     */
    private function addDirectory($dirname, $deep = true)
    {
        foreach (\scandir($dirname) ?: [] as $filename) {
            if ($filename === "." || $filename === "..") {
                continue;
            }

            $this->addFile($dirname . \DIRECTORY_SEPARATOR . $filename, $deep);
        }
    }

    /**
     * Suppresses files from its destinations.
     * 
     * @return void
     */
    public function suppress()
    {
        foreach (\array_keys($this->files) as $destname) {
            foreach ($this->vars as $match => $value) {
                if (!\str_contains($destname, $match)) {
                    continue;
                }

                $destname = \str_replace($match, $value, $destname);                    

                if (file_exists($destname)) {
                    \unlink($destname);
                }
            }
        }
    }

    /**
     * Propagates files to its destinations.
     * 
     * @return void
     */
    public function propagate()
    {
        foreach ($this->files as $destname => $contents) {
            foreach ($this->vars as $match => $value) {
                $replace_destname = \str_contains($destname, $match);
                $replace_contents = \str_contains($contents, $match);

                if (!$replace_destname && !$replace_contents) {
                    continue; 
                }

                if ($replace_destname) {
                    $destname = \str_replace($match, $value, $destname);
                }

                if ($replace_contents) {
                    $contents = \str_replace($match, $value, $contents);
                }

                $dirname = dirname($destname);

                // Creats the directory if it doesn't exist.
                // It performs recursive creation if needed.
                if (!\is_dir($dirname)) {
                    \mkdir($dirname, recursive: true);
                }

                \file_put_contents($destname, $contents);
            }
        }
    }
}
