<?php

namespace Tests\Phunkie\Effect\Functions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function Phunkie\Effect\Functions\blocking\blocking;

class BlockingTest extends TestCase
{
    #[Test]
    public function it_runs_a_blocking_operation()
    {
        $io = blocking(function() {
            return 42;
        });

        $this->assertEquals(
            42,
            $io->unsafeRunSync()
        );
    }
} 
