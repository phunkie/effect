<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Functions\console;

use Phunkie\Effect\IO\IO;
use Phunkie\Types\ImmList;
use Phunkie\Types\Unit;

const printLn = '\Phunkie\Effect\Functions\console\printLn';

/**
 * Prints a message to stdout followed by a newline.
 *
 * @param string $message The message to print
 * @return IO<Unit>
 */
function printLn(string $message): IO
{
    return new IO(function () use ($message): Unit {
        print($message . PHP_EOL);

        return Unit();
    });
}

const printLines = '\Phunkie\Effect\Functions\console\printLines';

/**
 * Prints a list of messages to stdout, each followed by a newline.
 *
 * @param ImmList<string> $lines The lines to print
 * @return IO<Unit>
 */
function printLines(ImmList $lines): IO
{
    return new IO(function () use ($lines): Unit {
        $lines->withEach(fn ($message) => print($message . PHP_EOL));

        return Unit();
    });
}

const readLine = '\Phunkie\Effect\Functions\console\readLine';

/**
 * Reads a line from the input stream.
 *
 * @param string $prompt Optional prompt to display
 * @param resource|null $stream Optional input stream (defaults to STDIN)
 * @return IO<string>
 */
function readLine(string $prompt, $stream = null): IO
{
    return new IO(function () use ($prompt, $stream) {
        print($prompt);
        $inputStream = $stream ?? STDIN;
        $line = fgets($inputStream);

        return $line !== false ? rtrim($line, "\r\n") : '';
    });
}

const printError = '\Phunkie\Effect\Functions\console\printError';

/**
 * Prints an error message (red) to stdout.
 *
 * @param string $message The error message
 * @return IO<void>
 */
function printError(string $message): IO
{
    return new IO(fn () => print("\033[31mError: {$message}\033[0m" . PHP_EOL));
}

const printWarning = '\Phunkie\Effect\Functions\console\printWarning';

/**
 * Prints a warning message (yellow) to stdout.
 *
 * @param string $message The warning message
 * @return IO<void>
 */
function printWarning(string $message): IO
{
    return new IO(fn () => print("\033[33mWarning: {$message}\033[0m" . PHP_EOL));
}

const printSuccess = '\Phunkie\Effect\Functions\console\printSuccess';

/**
 * Prints a success message (green) to stdout.
 *
 * @param string $message The success message
 * @return IO<void>
 */
function printSuccess(string $message): IO
{
    return new IO(fn () => print("\033[32mSuccess: {$message}\033[0m" . PHP_EOL));
}

const printInfo = '\Phunkie\Effect\Functions\console\printInfo';

/**
 * Prints an info message (cyan) to stdout.
 *
 * @param string $message The info message
 * @return IO<void>
 */
function printInfo(string $message): IO
{
    return new IO(fn () => print("\033[36mInfo: {$message}\033[0m" . PHP_EOL));
}

const printDebug = '\Phunkie\Effect\Functions\console\printDebug';

/**
 * Prints a debug message (magenta) to stdout.
 *
 * @param string $message The debug message
 * @return IO<void>
 */
function printDebug(string $message): IO
{
    return new IO(fn () => print("\033[35mDebug: {$message}\033[0m" . PHP_EOL));
}

const printTable = '\Phunkie\Effect\Functions\console\printTable';

/**
 * Prints a formatted table to stdout.
 *
 * @param array<array<mixed>> $data 2D array of data to print
 * @return IO<void>
 */
function printTable(array $data): IO
{
    return new IO(function () use ($data) {
        if (count($data) === 0) {
            return;
        }

        $widths = [];
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, strlen((string)$cell));
            }
        }

        $format = "| " . implode(" | ", array_map(fn ($w) => "%-{$w}s", $widths)) . " |\n";
        $separator = "+" . implode("+", array_map(fn ($w) => str_repeat("-", $w + 2), $widths)) . "+\n";

        $output = $separator;
        foreach ($data as $row) {
            $output .= sprintf($format, ...array_map(fn ($cell) => (string)$cell, $row));
            if ($row === reset($data)) {
                $output .= $separator;
            }
        }
        $output .= $separator;

        print($output);
    });
}

const printProgress = '\Phunkie\Effect\Functions\console\printProgress';

/**
 * Prints a progress bar to stdout (in-place).
 *
 * @param int $current Current progress value
 * @param int $total Total value
 * @return IO<void>
 */
function printProgress(int $current, int $total): IO
{
    return new IO(function () use ($current, $total) {
        $width = 20;
        $progress = min(100, max(0, ($current / $total) * 100));
        $completed = floor(($progress / 100) * $width);
        $remaining = $width - $completed;

        $bar = str_repeat("=", (int) $completed) . ">" . str_repeat(" ", (int) $remaining);
        print("\rProgress: [{$bar}] " . round($progress) . "%");
    });
}

const printSpinner = '\Phunkie\Effect\Functions\console\printSpinner';

/**
 * Prints a spinner animation with a message to stdout.
 *
 * @param string $message The message to display next to spinner
 * @return IO<void>
 */
function printSpinner(string $message): IO
{
    return new IO(function () use ($message) {
        static $spinner = ['|', '/', '-', '\\'];
        static $i = 0;

        print("\r{$message} " . $spinner[$i]);
        $i = ($i + 1) % count($spinner);
    });
}
