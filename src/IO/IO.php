<?php

namespace Phunkie\Effect\IO;

class IO
{
    private $unsafeRun;

    public function __construct(callable $unsafeRun)
    {
        $this->unsafeRun = $unsafeRun;
    }

    public function unsafeRun()
    {
        return ($this->unsafeRun)();
    }
}
