<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\IO;

use function Phunkie\Effect\Functions\ioapp\arguments;
use function Phunkie\Effect\Functions\ioapp\option;

use Phunkie\Effect\IO\IOApp\Error;
use Phunkie\Effect\IO\IOApp\Options;
use Phunkie\Effect\IO\IOApp\ParsedOptions;
use Phunkie\Types\NonEmptyList;
use Phunkie\Validation\Validation;

/**
 * Base class for IO applications.
 *
 * Extend this class to create your own IO application.
 * The run method must return an IO that will be executed when the application starts.
 * The return value of the IO will be used as the application's exit code.
 *
 * Example Usage:
 * ```php
 * class MyApp extends IOApp
 * {
 *     public function define(): Validation
 *     {
 *         return arguments(
 *             option('f', 'file', 'Input file', Required),
 *             option('v', 'verbose', 'Verbose output', NoInput)
 *         );
 *     }
 *
 *     public function run(?array $args = []): IO
 *     {
 *         return io(function() use ($args) {
 *             $this->parse($args)->fold(
 *                 fn($errors) => $this->handleErrors($errors),
 *                 fn($opts) => $this->program($opts)
 *             );
 *         });
 *     }
 * }
 * ```
 */
abstract class IOApp
{
    public function __construct(private string $version = "0.0.1")
    {
    }

    /**
     * @return IO<int>
     */
    abstract public function run(?array $args = []): IO;

    /**
     * Defines the CLI arguments for the application.
     * Override this method to provide your own definitions.
     *
     * @return Validation<NonEmptyList<Error>, Options>
     */
    protected function define(): Validation
    {
        return arguments();
    }

    /**
     * Parses the CLI arguments based on the definitions from define().
     *
     * Example:
     * ```php
     * $result = $this->parse($argv);
     * $result->fold(
     *     fn($errors) => echo "Invalid arguments", // $errors is NonEmptyList<Error>
     *     fn($options) => $options->has('verbose') // $options is ParsedOptions
     * );
     * ```
     *
     * @param array $args The raw arguments (usually $argv)
     * @return IO<ParsedOptions> IO that resolves to parsed options, handling errors/help/version internally
     */
    protected function parse(array $args): IO
    {
        return new IO(function () use ($args) {
            $validation = $this->define()
                ->flatMap(fn ($options) => $options->parse($args));

            $validation->fold(
                fn ($errors) => exit($this->showUsage($errors)->unsafeRun())
            )(
                fn ($options) => match(true) {
                    $options->has('help') => exit($this->showUsage()->unsafeRun()),
                    $options->has('version') => exit($this->showVersion()->unsafeRun()),
                    default => null
                }
            );

            // If we reach here, validation was Success and no help/version flags
            return $validation->toOption()->get();
        });
    }

    /**
     * Low-level parse that returns Validation for advanced use cases.
     * Most users should use parse() instead.
     *
     * @param array $args The raw arguments (usually $argv)
     * @return Validation<NonEmptyList<Error>, ParsedOptions>
     */
    protected function parseValidation(array $args): Validation
    {
        return $this->define()
            ->flatMap(fn ($options) => $options->parse($args));
    }

    protected function showErrors(?NonEmptyList $errors = null): IO
    {
        return new IO(function () use ($errors) {
            if ($errors) {
                $errorMessages = $errors->map(fn (Error $e) => $e->message)->mkString(", ");
                fwrite(STDERR, "Error: " . $errorMessages . "\r\n\r\n");
            }
        });
    }

    protected function showVersion(): IO
    {
        return new IO(function () {
            echo $this->version . "\r\n";

            return 0;
        });
    }

    protected function showUsage(?NonEmptyList $errors = null): IO
    {
        return $this->showErrors($errors)->flatMap(function () {
            return new IO(function () {
                echo "Usage: application [options]\r\n\r\n";

                $this->define()
                    ->map(function (Options $options) {
                        echo $options->describe();
                    });

                return 1;
            });
        });
    }
}
