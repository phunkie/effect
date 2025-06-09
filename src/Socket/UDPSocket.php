<?php

namespace Phunkie\Effect\Socket;

use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\blocking\blocking;

class UDPSocket extends BaseSocket
{
    public static function create(string $host, int $port): IO
    {
        return io(function() use ($host, $port) {
            return blocking(function() use ($host, $port) {
                $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
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

    public function sendTo(string $data, string $host, int $port): IO
    {
        return io(function() use ($data, $host, $port) {
            return blocking(function() use ($data, $host, $port) {
                $bytes = socket_sendto($this->socket, $data, strlen($data), 0, $host, $port);
                if ($bytes === false) {
                    throw new \RuntimeException(
                        "Failed to send: " . socket_strerror(socket_last_error($this->socket))
                    );
                }
                return $bytes;
            });
        });
    }

    public function receiveFrom(int $length, string &$host, int &$port): IO
    {
        return io(function() use ($length, &$host, &$port) {
            return blocking(function() use ($length, &$host, &$port) {
                $data = '';
                $bytes = socket_recvfrom($this->socket, $data, $length, 0, $host, $port);
                if ($bytes === false) {
                    throw new \RuntimeException(
                        "Failed to receive: " . socket_strerror(socket_last_error($this->socket))
                    );
                }
                return $data;
            });
        });
    }
} 
