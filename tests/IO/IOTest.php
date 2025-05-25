<?php

namespace Tests\Phunkie\Effect\IO;

use Phunkie\Effect\IO\IO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class IOTest extends TestCase
{
    #[Test]
    public function it_can_create_an_io_from_a_callable_and_run_it()
    {
        $io = new IO(function() {
            return 42;
        });

        $this->assertEquals(42, $io->unsafeRun());
    }

    #[Test]
    public function it_can_create_an_io_with_side_effects()
    {
        $counter = 0;
        $io = new IO(function() use (&$counter) {
            $counter++;
            return $counter;
        });

        $this->assertEquals(1, $io->unsafeRun());
        $this->assertEquals(2, $io->unsafeRun());
    }
}
