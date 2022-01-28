<?php

namespace Imanghafoori\FileSystem;

use ErrorException;

class FakeFileSystem
{
    public static $putContent = [];

    public static $files = [];

    public static $pointers = [];

    public static function reset()
    {
        self::$putContent = [];
        self::$files = [];
        self::$pointers = [];
    }

    public static function file_put_contents($absPath, $newVersion)
    {
        self::$putContent[$absPath] = $newVersion;
    }

    public static function feof($stream)
    {
        $i = self::$pointers[$stream];

        return ! isset(self::$files[$stream][$i]);
    }

    public static function fopen($filename, $mode)
    {
        try {
            $lines = file($filename);
        } catch (ErrorException $e) {
            $lines = [];
        }

        self::$files[$filename] = $lines;
        self::$pointers[$filename] = 0;

        return $filename;
    }

    public static function fgets($stream)
    {
        $i = self::$pointers[$stream];
        $val = (self::$files[$stream][$i]);
        self::$pointers[$stream]++;

        return $val;
    }

    public static function fwrite($stream, $data)
    {
        return self::$files[$stream][] = $data;
    }

    public static function rename($from, $to)
    {
        self::$files[$to] = self::$files[$from];

        unset(self::$files[$from]);
    }

    public static function unlink($filename)
    {
        unset(self::$files[$filename]);
        unset(self::$pointers[$filename]);
    }

    public static function fclose($filename)
    {
        //unset(self::$files[$filename]);
        //unset(self::$pointers[$filename]);
    }
}
