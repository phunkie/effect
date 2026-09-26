<?php

namespace Tests\Unit\Phunkie\Effect\IO;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Phunkie\Effect\Functions\io\io;

use RuntimeException;

class RecoveryTest extends TestCase
{
    #[Test]
    public function handleErrorWith_recovers_with_another_effect(): void
    {
        $recovered = io(fn () => throw new RuntimeException('boom'))
            ->handleErrorWith(fn (RuntimeException $e) => io(fn () => 'recovered from ' . $e->getMessage()));

        $this->assertSame('recovered from boom', $recovered->unsafeRun());
        $this->assertSame(42, io(fn () => 42)->handleErrorWith(fn () => io(fn () => 0))->unsafeRun());
    }

    #[Test]
    public function recover_handles_only_the_named_exception_class(): void
    {
        $recover = fn ($e) => io(fn () => 'handled');

        $this->assertSame('handled', io(fn () => throw new InvalidArgumentException('bad'))->recover(InvalidArgumentException::class, $recover)->unsafeRun());

        $this->expectException(RuntimeException::class);
        io(fn () => throw new RuntimeException('other'))->recover(InvalidArgumentException::class, $recover)->unsafeRun();
    }

    #[Test]
    public function mapN_runs_the_effects_in_order_and_combines_their_results(): void
    {
        $log = [];
        $step = function (string $name) use (&$log) {
            return io(function () use (&$log, $name) {
                $log[] = $name;

                return strtoupper($name);
            });
        };

        $combined = $step('a')->mapN([$step('b'), $step('c')], fn (string $a, string $b, string $c) => $a . $b . $c);

        $this->assertSame([], $log);
        $this->assertSame('ABC', $combined->unsafeRun());
        $this->assertSame(['a', 'b', 'c'], $log);
    }
}
