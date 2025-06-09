<?php

namespace Phunkie\Effect\Socket;

use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\blocking\blocking;

class TCPSocket extends BaseSocket
{
    public static function create(string $host, int $port): IO
    {
        return io(function() use ($host, $port) {
            return blocking(function() use ($host, $port) {
                $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if ($socket === false) {
                    throw new \RuntimeException(
                        "Failed to create socket: " . socket_strerror(socket_last_error())
                    );
                }
                return new self($socket);
            });
        });
    }

    public function bind(string $host, int $port): IO
    {
        return io(function() use ($host, $port) {
            return blocking(function() use ($host, $port) {
                if (!socket_bind($this->socket, $host, $port)) {
                    throw new \RuntimeException(
                        "Failed to bind: " . socket_strerror(socket_last_error($this->socket))
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

    public function connect(string $host, int $port): IO
    {
        return io(function() use ($host, $port) {
            return blocking(function() use ($host, $port) {
                if (!socket_connect($this->socket, $host, $port)) {
                    throw new \RuntimeException(
                        "Failed to connect: " . socket_strerror(socket_last_error($this->socket))
                    );
                }
                return $this;
            });
        });
    }
} 
