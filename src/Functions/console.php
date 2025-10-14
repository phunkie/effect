<?php

namespace Phunkie\Effect\Functions\console;

use Phunkie\Effect\IO\IO;
use Phunkie\Types\ImmList;

const printLn = '\Phunkie\Effect\Functions\console\printLn';
function printLn(string $message): IO
{
    return new IO(fn () => print($message . PHP_EOL));
}

const printLines = '\Phunkie\Effect\Functions\console\printLines';
function printLines(ImmList $lines): IO
{
    return new IO(fn () =>
        $lines->withEach(fn ($message) => print($message . PHP_EOL)));
}

const readLine = '\Phunkie\Effect\Functions\console\readLine';
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
function printError(string $message): IO
{
    return new IO(fn () => print("\033[31mError: {$message}\033[0m" . PHP_EOL));
}

const printWarning = '\Phunkie\Effect\Functions\console\printWarning';
function printWarning(string $message): IO
{
    return new IO(fn () => print("\033[33mWarning: {$message}\033[0m" . PHP_EOL));
}

const printSuccess = '\Phunkie\Effect\Functions\console\printSuccess';
function printSuccess(string $message): IO
{
    return new IO(fn () => print("\033[32mSuccess: {$message}\033[0m" . PHP_EOL));
}

const printInfo = '\Phunkie\Effect\Functions\console\printInfo';
function printInfo(string $message): IO
{
    return new IO(fn () => print("\033[36mInfo: {$message}\033[0m" . PHP_EOL));
}

const printDebug = '\Phunkie\Effect\Functions\console\printDebug';
function printDebug(string $message): IO
{
    return new IO(fn () => print("\033[35mDebug: {$message}\033[0m" . PHP_EOL));
}

const printTable = '\Phunkie\Effect\Functions\console\printTable';
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
function printProgress(int $current, int $total): IO
{
    return new IO(function () use ($current, $total) {
        $width = 20;
        $progress = min(100, max(0, ($current / $total) * 100));
        $completed = floor(($progress / 100) * $width);
        $remaining = $width - $completed;

        $bar = str_repeat("=", $completed) . ">" . str_repeat(" ", $remaining);
        print("\rProgress: [{$bar}] " . round($progress) . "%");
    });
}

const printSpinner = '\Phunkie\Effect\Functions\console\printSpinner';
function printSpinner(string $message): IO
{
    return new IO(function () use ($message) {
        static $spinner = ['|', '/', '-', '\\'];
        static $i = 0;

        print("\r{$message} " . $spinner[$i]);
        $i = ($i + 1) % count($spinner);
    });
}
