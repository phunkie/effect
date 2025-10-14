<?php

namespace Tests\Phunkie\Effect\Functions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Phunkie\Effect\Functions\io\io;

use Phunkie\Effect\IO\IO;

use function Phunkie\Functions\functor\allAs;
use function Phunkie\Functions\functor\asVoid;
use function Phunkie\Functions\functor\fmap;
use function Phunkie\Functions\functor\zipWith;

use Phunkie\Types\Pair;
use Phunkie\Types\Unit;

class functorTest extends TestCase
{
    #[Test]
    public function it_maps_over_io_values_using_fmap()
    {
        $io = io(function () {
            return 42;
        });

        $double = function ($x) {
            return $x * 2;
        };

        $result = fmap($double)($io);

        $this->assertInstanceOf(IO::class, $result);
        $this->assertEquals(84, $result->unsafeRun());
    }

    #[Test]
    public function it_replaces_io_contents_with_constant_value_using_allAs()
    {
        $io = io(function () {
            return 42;
        });

        $result = allAs("x")($io);

        $this->assertInstanceOf(IO::class, $result);
        $this->assertEquals("x", $result->unsafeRun());
    }

    #[Test]
    public function it_discards_io_contents_replacing_with_unit_using_asVoid()
    {
        $io = io(function () {
            return 42;
        });

        $result = asVoid($io);

        $this->assertInstanceOf(IO::class, $result);
        $this->assertInstanceOf(Unit::class, $result->unsafeRun());
    }

    #[Test]
    public function it_zips_io_values_with_mapped_values_using_zipWith()
    {
        $io = io(function () {
            return 42;
        });

        $double = function ($x) {
            return $x * 2;
        };

        $result = zipWith($double)($io);

        $this->assertInstanceOf(IO::class, $result);
        $pair = $result->unsafeRun();
        $this->assertInstanceOf(Pair::class, $pair);
        $this->assertEquals(42, $pair->_1);
        $this->assertEquals(84, $pair->_2);
    }
}
