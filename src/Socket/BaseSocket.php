<?php

namespace Phunkie\Effect\Socket;

use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\io\io;

abstract class BaseSocket implements Socket
{
    protected \Socket $socket;

    public function __construct(\Socket $socket)
    {
        $this->socket = $socket;
    }

    public function read(int $length): IO
    {
        return io(function() use ($length) {
            $data = socket_read($this->socket, $length);
            if ($data === false) {
                throw new \RuntimeException("Failed to read from socket: " . socket_strerror(socket_last_error($this->socket)));
            }
            return $data;
        });
    }

    public function write(string $data): IO
    {
        return io(function() use ($data) {
            $bytes = socket_write($this->socket, $data, strlen($data));
            if ($bytes === false) {
                throw new \RuntimeException("Failed to write to socket: " . socket_strerror(socket_last_error($this->socket)));
            }
            return $bytes;
        });
    }

    public function close(): IO
    {
        return io(function() {
            socket_close($this->socket);
            return null;
        });
    }

    public function getResource()
    {
        return $this->socket;
    }
} 
