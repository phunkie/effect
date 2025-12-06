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

/**
 * Base class for IO applications.
 *
 * Extend this class to create your own IO application.
 * The run method must return an IO that will be executed when the application starts.
 * The return value of the IO will be used as the application's exit code.
 */
abstract class IOApp
{
    /**
     * The main entry point for the application.
     *
     * This method is called by the runtime and should return an IO effect
     * that produces an integer exit code.
     *
     * @param array<string>|null $args Command line arguments
     * @return IO<int> The exit code (0 for success, non-zero for failure)
     */
    abstract public function run(?array $args = []): IO;
}
