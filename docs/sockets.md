# Sockets

The socket module provides a functional interface to PHP's socket functions, making it easier to work with network sockets in a safe and composable way. The module supports TCP, UDP, and Unix Domain Sockets.

## Socket Types

### TCP Sockets

TCP sockets provide reliable, ordered, and error-checked delivery of data. They are suitable for applications that require guaranteed delivery and correct ordering of data.

```php
use function Phunkie\Effect\Functions\sockets\tcp_server;
use function Phunkie\Effect\Functions\sockets\tcp_client;

// Create a TCP server
$server = tcp_server('127.0.0.1', 8080)
    ->flatMap(fn($socket) => $socket->accept())
    ->flatMap(fn($client) => $client->write("Hello, World!"))
    ->flatMap(fn($client) => $client->close());

// Create a TCP client
$client = tcp_client('127.0.0.1', 8080)
    ->flatMap(fn($socket) => $socket->read(1024))
    ->flatMap(fn($data) => $socket->close());
```

### UDP Sockets

UDP sockets provide connectionless, unreliable datagram service. They are suitable for applications that can tolerate some data loss but require low latency.

```php
use function Phunkie\Effect\Functions\sockets\udp_socket;

$host = '';
$port = 0;

// Create a UDP server
$server = udp_socket('127.0.0.1', 8081)
    ->flatMap(function($socket) use (&$host, &$port) {
        return $socket->receiveFrom(1024, $host, $port)
            ->flatMap(function($data) use ($socket, $host, $port) {
                return $socket->sendTo("Echo: $data", $host, $port);
            });
    });

// Create a UDP client
$client = udp_socket('127.0.0.1', 8082)
    ->flatMap(fn($socket) => $socket->sendTo("Hello, UDP!", '127.0.0.1', 8081))
    ->flatMap(function($socket) use (&$host, &$port) {
        return $socket->receiveFrom(1024, $host, $port);
    });
```

### Unix Domain Sockets

Unix Domain Sockets provide inter-process communication on the same machine. They are more efficient than TCP/IP sockets for local communication.

```php
use function Phunkie\Effect\Functions\sockets\unix_server;
use function Phunkie\Effect\Functions\sockets\unix_client;

$path = '/tmp/test.sock';

// Create a Unix Domain server
$server = unix_server($path)
    ->flatMap(fn($socket) => $socket->accept())
    ->flatMap(fn($client) => $client->write("Hello, Unix!"))
    ->flatMap(fn($client) => $client->close());

// Create a Unix Domain client
$client = unix_client($path)
    ->flatMap(fn($socket) => $socket->read(1024))
    ->flatMap(fn($data) => $socket->close());
```

## Socket Options

The socket module provides functions to configure various socket options:

```php
use function Phunkie\Effect\Functions\sockets\set_timeout;
use function Phunkie\Effect\Functions\sockets\set_reuse_address;
use function Phunkie\Effect\Functions\sockets\set_keep_alive;
use function Phunkie\Effect\Functions\sockets\set_tcp_nodelay;
use function Phunkie\Effect\Functions\sockets\set_buffer_size;

$socket = tcp_server('127.0.0.1', 8080)
    ->flatMap(fn($socket) => set_reuse_address($socket))
    ->flatMap(fn($socket) => set_keep_alive($socket))
    ->flatMap(fn($socket) => set_tcp_nodelay($socket))
    ->flatMap(fn($socket) => set_buffer_size($socket, 8192))
    ->flatMap(fn($socket) => set_timeout($socket, 5));
```

## Socket Lifecycle

The socket module handles the complete lifecycle of sockets:

1. **Creation**: Sockets are created using factory functions like `tcp_server`, `tcp_client`, `udp_socket`, etc.
2. **Configuration**: Socket options are set using functions like `set_timeout`, `set_reuse_address`, etc.
3. **Operation**: Sockets are used for reading and writing data.
4. **Cleanup**: Sockets are automatically closed when they go out of scope, but you can also explicitly close them using the `close` method.

## Best Practices

1. **Resource Management**: Always use the `bracket` pattern to ensure proper cleanup of socket resources:

```php
use function Phunkie\Effect\Functions\io\bracket;

$server = bracket(
    tcp_server('127.0.0.1', 8080),
    fn($socket) => $socket->accept()
        ->flatMap(fn($client) => $client->write("Hello, World!"))
        ->flatMap(fn($client) => $client->close()),
    fn($socket) => $socket->close()
);
```

2. **Error Handling**: Use the `attempt` function to handle socket errors:

```php
use function Phunkie\Effect\Functions\io\attempt;

$client = tcp_client('127.0.0.1', 8080)
    ->flatMap(fn($socket) => attempt(
        fn() => $socket->read(1024),
        fn($error) => "Error: " . $error->getMessage()
    ))
    ->flatMap(fn($socket) => $socket->close());
```

3. **Timeout Configuration**: Always set appropriate timeouts to prevent blocking operations from hanging:

```php
$socket = tcp_server('127.0.0.1', 8080)
    ->flatMap(fn($socket) => set_timeout($socket, 5))
    ->flatMap(fn($socket) => set_send_timeout($socket, 5));
```

4. **Buffer Sizes**: Configure appropriate buffer sizes based on your application's needs:

```php
$socket = tcp_server('127.0.0.1', 8080)
    ->flatMap(fn($socket) => set_buffer_size($socket, 8192));
```

5. **Address Reuse**: Enable address reuse to avoid "Address already in use" errors:

```php
$socket = tcp_server('127.0.0.1', 8080)
    ->flatMap(fn($socket) => set_reuse_address($socket));
```

6. **Keep-Alive**: Enable keep-alive for long-lived connections:

```php
$socket = tcp_server('127.0.0.1', 8080)
    ->flatMap(fn($socket) => set_keep_alive($socket));
```

7. **TCP No-Delay**: Disable Nagle's algorithm for low-latency applications:

```php
$socket = tcp_server('127.0.0.1', 8080)
    ->flatMap(fn($socket) => set_tcp_nodelay($socket));
```

## Limitations

1. The socket module requires the PHP sockets extension to be installed and enabled.
2. Unix Domain Sockets are only available on Unix-like systems.
3. Some socket options may not be available on all platforms.
4. The module does not support SSL/TLS directly. Use a separate SSL/TLS library for secure communication.
5. The module does not support IPv6 directly. Use IPv4 addresses for now.
6. The module does not support multicast or broadcast directly. Use raw sockets for these features.
7. The module does not support non-blocking I/O directly. Use the `blocking` function to handle blocking operations.
8. The module does not support socket pairs directly. Use Unix Domain Sockets for this feature.
9. The module does not support socket timeouts directly. Use the `set_timeout` function to handle timeouts.
10. The module does not support socket buffers directly. Use the `set_buffer_size` function to handle buffers. 