<?php

namespace Imanghafoori\FileSystem;

class FileManipulator
{
    public static $fileSystem = RealFileSystem::class;

    public static function removeLine($file, $_lineNumber = null)
    {
        $lineChanger = function ($currentLineNum) use ($_lineNumber) {
            // Replace only the first occurrence in the file
            if ($currentLineNum === $_lineNumber) {
                return '';
            }
        };

        return self::applyToEachLine($file, $lineChanger);
    }

    public static function replaceFirst($absPath, $search, $replace = '', $_line = null)
    {
        $lineChanger = function ($lineNum, $line, $isReplaced) use ($search, $replace, $_line) {
            // Replace only the first occurrence in the file
            if (! $isReplaced && strstr($line, $search)) {
                if (! $_line || $lineNum === $_line) {
                    return self::replaceFirstInStr($search, $replace, $line);
                }
            }
        };

        return self::applyToEachLine($absPath, $lineChanger);
    }

    public static function insertAtLine($absPath, $newLine, $atLine)
    {
        $lineChanger = function ($lineNum, $currentLine) use ($newLine, $atLine) {
            if ($lineNum == $atLine) {
                return $newLine.PHP_EOL.$currentLine;
            }
        };

        return self::applyToEachLine($absPath, $lineChanger);
    }

    private static function applyToEachLine($absPath, $lineChanger)
    {
        $fs = FileSystem::$fileSystem;
        $reading = $fs::fopen($absPath, 'r');
        $tmpFile = $fs::fopen($absPath.'._tmp', 'w');

        $isReplaced = false;

        $lineNum = 0;
        while (! $fs::feof($reading)) {
            $lineNum++;
            $line = $fs::fgets($reading);

            $newLine = $lineChanger($lineNum, $line, $isReplaced);
            if (is_string($newLine)) {
                $line = $newLine;
                $isReplaced = true;
            }
            // Copy the entire file to the end
            $fs::fwrite($tmpFile, $line);
        }
        $fs::fclose($reading);
        $fs::fclose($tmpFile);
        // Might as well not overwrite the file if we didn't replace anything
        if ($isReplaced) {
            $fs::rename($absPath.'._tmp', $absPath);
        } else {
            $fs::unlink($absPath.'._tmp');
        }

        return $isReplaced;
    }

    private static function replaceFirstInStr($search, $replace, $subject)
    {
        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }
}
