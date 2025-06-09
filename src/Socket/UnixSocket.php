<?php

namespace Phunkie\Effect\Socket;

use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\blocking\blocking;

class UnixSocket extends BaseSocket
{
    public static function create(string $path, bool $stream = true): IO
    {
        return io(function() use ($path, $stream) {
            return blocking(function() use ($path, $stream) {
                $type = $stream ? SOCK_STREAM : SOCK_DGRAM;
                $socket = socket_create(AF_UNIX, $type, 0);
                if ($socket === false) {
                    throw new \RuntimeException(
                        "Failed to create socket: " . socket_strerror(socket_last_error())
                    );
                }
                return new self($socket);
            });
        });
    }

    public function bind(string $path): IO
    {
        return io(function() use ($path) {
            return blocking(function() use ($path) {
                if (!socket_bind($this->socket, $path)) {
                    throw new \RuntimeException(
                        "Failed to bind: " . socket_strerror(socket_last_error($this->socket))
                    );
                }
                return $this;
            });
        });
    }

    public function connect(string $path): IO
    {
        return io(function() use ($path) {
            return blocking(function() use ($path) {
                if (!socket_connect($this->socket, $path)) {
                    throw new \RuntimeException(
                        "Failed to connect: " . socket_strerror(socket_last_error($this->socket))
                    );
                }
                return $this;
            });
        });
    }

    public function listen(int $backlog = 5): IO
    {
        return io(function() use ($backlog) {
            return blocking(function() use ($backlog) {
                if (!socket_listen($this->socket, $backlog)) {
                    throw new \RuntimeException(
                        "Failed to listen: " . socket_strerror(socket_last_error($this->socket))
                    );
                }
                return $this;
            });
        });
    }

    public function accept(): IO
    {
        return io(function() {
            return blocking(function() {
                $client = socket_accept($this->socket);
                if ($client === false) {
                    throw new \RuntimeException(
                        "Failed to accept: " . socket_strerror(socket_last_error($this->socket))
                    );
                }
                return new self($client);
            });
        });
    }
} 
