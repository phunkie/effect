<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Phunkie\Effect\Functions;

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
