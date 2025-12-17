<?php

namespace Phunkie\Effect\IO\IOApp;

class Error
{
    public function __construct(public readonly string $message)
    {
    }
}
