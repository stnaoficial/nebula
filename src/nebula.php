<?php

final class Nebula
{
    /**
     * The title of the tool.
     */
    public const TITLE = "Nebula";

    /**
     * The description of the tool.
     */
    public const DESCRIPTION = "A CLI tool to easily suppress and propagate file descendants.";

    /**
     * The short version of the tool.
     */
    public const SHORT_VERSION = "0.1.0";

    /**
     * The long version of the tool.
     */
    public const LONG_VERSION = "beta-0.1.0 (2024-04-07)";

    /**
     * The extension of the files.
     */
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
     * @throws \UnexpectedValueException  If an error occurs when interpretating
     *                                    some file or directory.
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
     * @throws \UnexpectedValueException  If failed  to  assign  a value to  the
     *                                    variable.
     * 
     * @return string Returns the variable value.
     */
    private function askVariable($name)
    {
        \fwrite(\STDOUT, \sprintf("Enter a value for variable [%s]: ", $name));

        if (!$value = \fgets(\STDIN)) {
            throw new \UnexpectedValueException(\sprintf(
                "Failed to assign a value to variable [%s].", $name
            ));
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
     * @throws \UnexpectedValueException If failed to parse the destination name.
     * @throws \InvalidArgumentException If file is empty.
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

        $destname = \trim(\str_replace("\\", \DIRECTORY_SEPARATOR, $destname));

        if (empty($destname)) {
            throw new \UnexpectedValueException(\sprintf(
                "Failed to parse destination name of file %s.", $filename
            ));
        }

        $this->fetchVariables($destname);

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
     * @throws \UnexpectedValueException  If an error occurs when interpretating
     *                                    the directory.
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

                if (\file_exists($destname)) {
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

                $dirname = \dirname($destname);

                if (!\is_dir($dirname)) {
                    \mkdir($dirname, recursive: true);
                }

                \file_put_contents($destname, $contents);
            }
        }
    }
}
