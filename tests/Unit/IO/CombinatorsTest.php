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

use function Phunkie\Effect\Functions\blocking\blocking;
use function Phunkie\Effect\Functions\io\io;

class CombinatorsTest extends TestCase
{
    #[Test]
    public function product_r_runs_both_and_keeps_the_second_result()
    {
        $log = [];
        $logging = function (string $entry) use (&$log) {
            return io(function () use (&$log, $entry) {
                $log[] = $entry;

                return $entry;
            });
        };

        $this->assertSame('second', $logging('first')->productR($logging('second'))->unsafeRun());
        $this->assertSame(['first', 'second'], $log);
    }

    #[Test]
    public function delay_waits_before_running()
    {
        $started = hrtime(true);

        $this->assertSame('late', io(fn () => 'late')->delay(20)->unsafeRun());
        $this->assertGreaterThanOrEqual(20, (hrtime(true) - $started) / 1_000_000);
    }

    #[Test]
    public function both_pairs_the_results_this_effect_first()
    {
        $log = [];
        $logging = function (string $entry) use (&$log) {
            return io(function () use (&$log, $entry) {
                $log[] = $entry;

                return $entry;
            });
        };

        $this->assertSame(['a', 'b'], $logging('a')->both($logging('b'))->unsafeRun());
        $this->assertSame(['a', 'b'], $log);
        $this->assertSame([1, 2], blocking(fn () => 1)->both(blocking(fn () => 2))->unsafeRun());
    }
}
