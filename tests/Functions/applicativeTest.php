<?php

namespace Tests\Phunkie\Effect\Functions;

use Phunkie\Effect\IO\IO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

use const Phunkie\Effect\Functions\io\IO;

use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Functions\applicative\pure;
use function Phunkie\Functions\applicative\ap;
use function Phunkie\Functions\applicative\map2;

class applicativeTest extends TestCase
{
    #[Test]
    public function it_lifts_values_into_io_using_pure()
    {
        $result = pure(IO)(42);

        $this->assertInstanceOf(IO::class, $result);
        $this->assertEquals(42, $result->unsafeRun());
    }

    #[Test]
    public function it_applies_io_of_function_to_io_of_value_using_ap()
    {
        $iof = io(function () {
            return function ($x) {
                return $x * 2;
            };
        });

        $iox = io(function () {
            return 21;
        });

        $result = ap($iox)($iof);

        $this->assertInstanceOf(IO::class, $result);
        $this->assertEquals(42, $result->unsafeRun());
    }

    #[Test]
    public function it_combines_two_io_values_using_liftA2()
    {
        $iox = io(function () {
            return 21;
        });

        $ioy = io(function () {
            return 2;
        });

        $result = map2(function ($x, $y) {
            return $x * $y;
        })($iox)($ioy);

        $this->assertInstanceOf(IO::class, $result);
        $this->assertEquals(42, $result->unsafeRun());
    }
}
