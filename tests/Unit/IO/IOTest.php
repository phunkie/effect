<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Phunkie\Effect\IO;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Phunkie\Effect\IO\IO;
use Phunkie\Types\Kind;

class IOTest extends TestCase
{
    #[Test]
    public function it_can_create_an_io_from_a_callable_and_run_it()
    {
        $io = new IO(function () {
            return 42;
        });

        $this->assertEquals(42, $io->unsafeRun());
    }

    #[Test]
    public function it_can_create_an_io_with_side_effects()
    {
        $counter = 0;
        $io = new IO(function () use (&$counter) {
            $counter++;

            return $counter;
        });

        $this->assertEquals(1, $io->unsafeRun());
        $this->assertEquals(2, $io->unsafeRun());
    }

    #[Test]
    public function it_is_a_phunkie_kind()
    {
        $io = new IO(function () {
            return 42;
        });

        $this->assertInstanceOf(Kind::class, $io);
        $this->assertEquals(1, $io->getTypeArity());
        $this->assertEquals(['A'], $io->getTypeVariables());
    }

    #[Test]
    public function it_handles_errors_with_attempt()
    {
        $io = new IO(function () {
            throw new \RuntimeException('test error');
        });

        $result = $io->attempt()->unsafeRun();

        $this->assertTrue($result->isLeft());

        $this->assertEquals('test error', $result->fold(fn ($e) => $e->getMessage())(fn ($x) => $x));
    }

    #[Test]
    public function it_can_start_computation_in_background()
    {
        $counter = 0;
        $io = new IO(function () use (&$counter) {
            $counter++;

            return $counter;
        });

        // Start returns IO<AsyncHandle<A>>
        $handleIO = $io->start();
        $this->assertInstanceOf(IO::class, $handleIO);

        // Running the IO gives us an AsyncHandle
        $handle = $handleIO->unsafeRun();
        $this->assertInstanceOf(\Phunkie\Effect\Concurrent\AsyncHandle::class, $handle);

        // Counter hasn't incremented yet (lazy execution)
        $this->assertEquals(0, $counter);

        // Awaiting the handle executes the computation
        $result = $handle->await();
        $this->assertEquals(1, $result);
        $this->assertEquals(1, $counter);
    }

    #[Test]
    public function it_can_start_and_continue_without_blocking()
    {
        $log = [];

        $slowIO = new IO(function () use (&$log) {
            $log[] = 'slow-start';
            // Simulate slow work
            usleep(10000); // 10ms
            $log[] = 'slow-end';

            return 'slow-result';
        });

        $fastIO = new IO(function () use (&$log) {
            $log[] = 'fast';

            return 'fast-result';
        });

        // Start slow work in background
        $program = $slowIO->start()->flatMap(function ($handle) use ($fastIO, &$log) {
            // Do fast work while slow work runs
            return $fastIO->map(function ($fastResult) use ($handle, &$log) {
                // Now await slow work
                $slowResult = $handle->await();

                return [$slowResult, $fastResult];
            });
        });

        $result = $program->unsafeRun();

        $this->assertEquals(['slow-result', 'fast-result'], $result);
        // Note: In FiberExecutionContext, the fiber executes when await() is called
        // So the actual order is: fast, slow-start, slow-end
        $this->assertEquals(['fast', 'slow-start', 'slow-end'], $log);
    }
}
