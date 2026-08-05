<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Phunkie\Effect\Ops\IO;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Phunkie\Effect\IO\IO;
use RuntimeException;

class MonadErrorOpsTest extends TestCase
{
    #[Test]
    public function it_passes_the_value_through_when_the_predicate_holds()
    {
        $io = new IO(fn () => 42);

        $ensured = $io->ensure(fn ($x) => $x > 0, new RuntimeException("must be positive"));

        $this->assertEquals(42, $ensured->unsafeRun());
    }

    #[Test]
    public function it_raises_the_error_when_the_predicate_fails()
    {
        $io = new IO(fn () => -1);

        $ensured = $io->ensure(fn ($x) => $x > 0, new RuntimeException("must be positive"));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("must be positive");
        $ensured->unsafeRun();
    }

    #[Test]
    public function it_does_not_run_the_effect_until_the_ensured_io_is_run()
    {
        $ran = false;
        $io = new IO(function () use (&$ran) {
            $ran = true;

            return -1;
        });

        $io->ensure(fn ($x) => $x > 0, new RuntimeException("boom"));

        $this->assertFalse($ran, "ensure must stay lazy and not run the effect");
    }

    #[Test]
    public function it_is_recoverable_through_attempt()
    {
        $io = new IO(fn () => -1);

        $recovered = $io
            ->ensure(fn ($x) => $x > 0, new RuntimeException("must be positive"))
            ->attempt()
            ->map(fn ($validation) => $validation->fold(fn ($e) => $e->getMessage())(fn ($a) => "ok: $a"));

        $this->assertEquals("must be positive", $recovered->unsafeRun());
    }

    #[Test]
    public function it_builds_the_error_from_the_offending_value_with_ensure_or()
    {
        $io = new IO(fn () => -5);

        $ensured = $io->ensureOr(fn ($x) => $x > 0, fn ($x) => new RuntimeException("bad value: $x"));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("bad value: -5");
        $ensured->unsafeRun();
    }
}
