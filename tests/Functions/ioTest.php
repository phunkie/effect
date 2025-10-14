<?php

namespace Tests\Phunkie\Effect\Functions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Phunkie\Effect\Functions\io\io;

use Phunkie\Effect\IO\IO;

class ioTest extends TestCase
{
    #[Test]
    public function it_creates_an_io_from_a_callable()
    {
        $io = io(function () {
            return 42;
        });

        $this->assertInstanceOf(IO::class, $io);
        $this->assertEquals(42, $io->unsafeRun());
    }

    #[Test]
    public function it_creates_an_io_from_a_plain_value()
    {
        $io = io(42);

        $this->assertInstanceOf(IO::class, $io);
        $this->assertEquals(42, $io->unsafeRun());
    }
}
