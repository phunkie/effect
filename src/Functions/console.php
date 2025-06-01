<?php

namespace Phunkie\Effect\Functions\console;

use Phunkie\Effect\IO\IO;

/**
 * Prints a line to the console
 */
function printLn(string $message): IO
{
    return new IO(fn() => print($message . PHP_EOL));
}

/**
 * Reads a line from the console
 */
function readLine(string $prompt): IO
{
    return new IO(function() use ($prompt) {
        print($prompt);
        return trim(fgets(STDIN));
    });
}

/**
 * Prints an error message to the console
 */
function printError(string $message): IO
{
    return new IO(fn() => print("\033[31mError: {$message}\033[0m" . PHP_EOL));
}

/**
 * Prints a warning message to the console
 */
function printWarning(string $message): IO
{
    return new IO(fn() => print("\033[33mWarning: {$message}\033[0m" . PHP_EOL));
}

/**
 * Prints a success message to the console
 */
function printSuccess(string $message): IO
{
    return new IO(fn() => print("\033[32mSuccess: {$message}\033[0m" . PHP_EOL));
}

/**
 * Prints an info message to the console
 */
function printInfo(string $message): IO
{
    return new IO(fn() => print("\033[36mInfo: {$message}\033[0m" . PHP_EOL));
}

/**
 * Prints a debug message to the console
 */
function printDebug(string $message): IO
{
    return new IO(fn() => print("\033[35mDebug: {$message}\033[0m" . PHP_EOL));
}

/**
 * Prints a table to the console
 */
function printTable(array $data): IO
{
    return new IO(function() use ($data) {
        if (empty($data)) {
            return;
        }

        $widths = [];
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, strlen((string)$cell));
            }
        }

        $format = "| " . implode(" | ", array_map(fn($w) => "%-{$w}s", $widths)) . " |\n";
        $separator = "+" . implode("+", array_map(fn($w) => str_repeat("-", $w + 2), $widths)) . "+\n";

        $output = $separator;
        foreach ($data as $row) {
            $output .= sprintf($format, ...array_map(fn($cell) => (string)$cell, $row));
            if ($row === reset($data)) {
                $output .= $separator;
            }
        }
        $output .= $separator;

        print($output);
    });
}

/**
 * Prints a progress bar to the console
 */
function printProgress(int $current, int $total): IO
{
    return new IO(function() use ($current, $total) {
        $width = 20;
        $progress = min(100, max(0, ($current / $total) * 100));
        $completed = floor(($progress / 100) * $width);
        $remaining = $width - $completed;
        
        $bar = str_repeat("=", $completed) . ">" . str_repeat(" ", $remaining);
        print("\rProgress: [{$bar}] " . round($progress) . "%");
    });
}

/**
 * Prints a spinner to the console
 */
function printSpinner(string $message): IO
{
    return new IO(function() use ($message) {
        static $spinner = ['|', '/', '-', '\\'];
        static $i = 0;
        
        print("\r{$message} " . $spinner[$i]);
        $i = ($i + 1) % count($spinner);
    });
} 
