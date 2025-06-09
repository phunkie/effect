<?php

namespace Phunkie\Effect\Tests\Socket;

use PHPUnit\Framework\TestCase;
use Phunkie\Effect\Socket\BaseSocket;
use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\io\io;
use PHPUnit\Framework\Attributes\Test;

class SocketTest extends TestCase
{
    #[Test]
    public function it_performs_basic_socket_operations(): void
    {
        $socket = new class extends BaseSocket {
            private $data = '';
            private $closed = false;

            public function __construct()
            {
                $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if ($sock === false) {
                    throw new \RuntimeException("Failed to create socket: " . socket_strerror(socket_last_error()));
                }
                parent::__construct($sock);
            }

            public function read(int $length): IO
            {
                return io(fn() => substr($this->data, 0, $length));
            }

            public function write(string $data): IO
            {
                $this->data = $data;
                return io(fn() => strlen($data));
            }

            public function close(): IO
            {
                $this->closed = true;
                return io(fn() => null);
            }

            public function isClosed(): bool
            {
                return $this->closed;
            }
        };

        $result = $socket->write("Hello")
            ->flatMap(fn() => $socket->read(5))
            ->flatMap(fn($data) => $socket->close()->map(fn() => $data))
            ->unsafeRun();

        $this->assertEquals("Hello", $result);
        $this->assertTrue($socket->isClosed());
    }
}
