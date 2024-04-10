<?php

namespace Nebula\Support;

final class Path
{
    /**
     * Joins paths.
     * 
     * @param string $paths The paths to join.
     * 
     * @return string Returns the joined path.
     */
    public static function join(...$paths)
    {
        return \implode(\DIRECTORY_SEPARATOR, \array_filter($paths, "trim"));
    }
}