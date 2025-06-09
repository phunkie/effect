<?php

namespace Phunkie\Effect\Functions\sockets;

use Phunkie\Effect\IO\IO;
use Phunkie\Effect\Socket\TCPSocket;
use Phunkie\Effect\Socket\UDPSocket;
use Phunkie\Effect\Socket\UnixSocket;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\blocking\blocking;

function tcp_server(string $host, int $port, int $backlog = 5): IO
{
    return io(function() use ($host, $port, $backlog) {
        return blocking(function() use ($host, $port, $backlog) {
            return TCPSocket::create($host, $port)
                ->flatMap(fn($socket) => $socket->bind($host, $port))
                ->flatMap(fn($socket) => $socket->listen($backlog));
        });
    });
}

function tcp_client(string $host, int $port): IO
{
    return io(function() use ($host, $port) {
        return blocking(function() use ($host, $port) {
            return TCPSocket::create($host, $port)
                ->flatMap(fn($socket) => $socket->connect($host, $port));
        });
    });
}

function udp_socket(string $host, int $port): IO
{
    return io(function() use ($host, $port) {
        return blocking(function() use ($host, $port) {
            return UDPSocket::create($host, $port)
                ->flatMap(fn($socket) => $socket->bind($host, $port));
        });
    });
}

function unix_server(string $path, bool $stream = true, int $backlog = 5): IO
{
    return io(function() use ($path, $stream, $backlog) {
        return blocking(function() use ($path, $stream, $backlog) {
            return UnixSocket::create($path, $stream)
                ->flatMap(fn($socket) => $socket->bind($path))
                ->flatMap(fn($socket) => $socket->listen($backlog));
        });
    });
}

function unix_client(string $path, bool $stream = true): IO
{
    return io(function() use ($path, $stream) {
        return blocking(function() use ($path, $stream) {
            return UnixSocket::create($path, $stream)
                ->flatMap(fn($socket) => $socket->connect($path));
        });
    });
}

function set_option($socket, int $level, int $optname, $optval): IO
{
    return io(function() use ($socket, $level, $optname, $optval) {
        return blocking(function() use ($socket, $level, $optname, $optval) {
            if (!socket_set_option($socket->getResource(), $level, $optname, $optval)) {
                throw new \RuntimeException(
                    "Failed to set option: " . socket_strerror(socket_last_error($socket->getResource()))
                );
            }
            return $socket;
        });
    });
}

function get_option($socket, int $level, int $optname): IO
{
    return io(function() use ($socket, $level, $optname) {
        return blocking(function() use ($socket, $level, $optname) {
            $optval = socket_get_option($socket->getResource(), $level, $optname);
            if ($optval === false) {
                throw new \RuntimeException(
                    "Failed to get option: " . socket_strerror(socket_last_error($socket->getResource()))
                );
            }
            return $optval;
        });
    });
}

function set_timeout($socket, int $seconds, int $microseconds = 0): IO
{
    return set_option($socket, SOL_SOCKET, SO_RCVTIMEO, [
        'sec' => $seconds,
        'usec' => $microseconds
    ]);
}

function set_send_timeout($socket, int $seconds, int $microseconds = 0): IO
{
    return set_option($socket, SOL_SOCKET, SO_SNDTIMEO, [
        'sec' => $seconds,
        'usec' => $microseconds
    ]);
}

function set_reuse_address($socket, bool $reuse = true): IO
{
    return set_option($socket, SOL_SOCKET, SO_REUSEADDR, $reuse ? 1 : 0);
}

function set_keep_alive($socket, bool $keep_alive = true): IO
{
    return set_option($socket, SOL_SOCKET, SO_KEEPALIVE, $keep_alive ? 1 : 0);
}

function set_tcp_nodelay($socket, bool $nodelay = true): IO
{
    return set_option($socket, SOL_TCP, TCP_NODELAY, $nodelay ? 1 : 0);
}

function set_buffer_size($socket, int $size): IO
{
    return set_option($socket, SOL_SOCKET, SO_RCVBUF, $size)
        ->flatMap(fn($socket) => set_option($socket, SOL_SOCKET, SO_SNDBUF, $size));
} 
