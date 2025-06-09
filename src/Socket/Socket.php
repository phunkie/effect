<?php

namespace Phunkie\Effect\Socket;

use Phunkie\Effect\IO\IO;

interface Socket
{
    public function read(int $length): IO;
    public function write(string $data): IO;
    public function close(): IO;
    public function getResource();
} 
