<?php

namespace Phunkie\Effect\IOApp
{
    const ExitSuccess = 0;       // Success
    const ExitFailure = 1;       // General error
    const ExitMisuse = 2;        // Misuse of shell builtins
    const ExitCannotExec = 126;  // Command invoked cannot execute
    const ExitNotFound = 127;    // Command not found
    const ExitInvalid = 128;     // Invalid exit argument
    const ExitInterrupted = 130; // Script terminated by Control-C
}

namespace
{
    $functions = glob(__DIR__ . '/*.php');
    foreach ($functions as $function) {
        if ($function !== __FILE__) {
            require_once $function;
        }
    }
}
