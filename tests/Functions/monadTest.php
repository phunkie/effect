<?php

namespace Tests\Phunkie\Effect\Functions;

use Phunkie\Effect\IO\IO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Functions\monad\bind;
use function Phunkie\Functions\monad\flatten;

class monadTest extends TestCase
{
    #[Test]
    public function it_chains_io_operations_using_bind()
    {
        $io = io(function() {
            return 21;
        });

        $result = bind(function($x) {
            return io(function() use ($x) {
                return $x * 2;
            });
        })($io);

        $this->assertInstanceOf(IO::class, $result);
        $this->assertEquals(42, $result->unsafeRun());
    }

    #[Test]
    public function it_flattens_nested_io_using_join()
    {
        $io = io(function() {
            return io(function() {
                return 42;
            });
        });

        $result = flatten($io);

        $this->assertInstanceOf(IO::class, $result);
        $this->assertEquals(42, $result->unsafeRun());
    }
} 
