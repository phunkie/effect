<?php

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
     * @return IO<int>
     */
    abstract public function run(): IO;
}
