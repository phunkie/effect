<?php

namespace Tests\Phunkie\Effect\IO;

use Phunkie\Effect\IO\IO;
use Phunkie\Types\Kind;
use Phunkie\Validation\Failure;
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

    #[Test]
    public function it_is_a_phunkie_kind()
    {
        $io = new IO(function() {
            return 42;
        });

        $this->assertInstanceOf(Kind::class, $io);
        $this->assertEquals(1, $io->getTypeArity());
        $this->assertEquals(['A'], $io->getTypeVariables());
    }

    #[Test]
    public function it_handles_errors_with_attempt()
    {
        $io = new IO(function() {
            throw new \RuntimeException('test error');
        });

        $result = $io->attempt()->unsafeRun();

        $this->assertTrue($result->isLeft());
        
        $this->assertEquals('test error', $result->fold(fn($e) => $e->getMessage())(fn($x) => $x));
    }
}
