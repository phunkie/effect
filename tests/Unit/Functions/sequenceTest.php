<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Phunkie\Effect\Functions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Phunkie\Effect\Functions\blocking\blocking;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\io\sequence;
use function Phunkie\Effect\Functions\io\traverse;

class sequenceTest extends TestCase
{
    #[Test]
    public function sequence_turns_a_list_of_effects_into_an_effect_of_a_list()
    {
        $this->assertSame([1, 2, 3], sequence([io(fn () => 1), io(fn () => 2), io(fn () => 3)])->unsafeRun());
    }

    #[Test]
    public function sequence_runs_the_effects_in_order_and_only_when_run()
    {
        $log = [];
        $logging = function (string $entry) use (&$log) {
            return io(function () use (&$log, $entry) {
                $log[] = $entry;

                return $entry;
            });
        };

        $all = sequence(['first' => $logging('a'), 'second' => $logging('b')]);
        $this->assertSame([], $log);

        $this->assertSame(['first' => 'a', 'second' => 'b'], $all->unsafeRun());
        $this->assertSame(['a', 'b'], $log);
    }

    #[Test]
    public function sequence_awaits_blocking_effects()
    {
        $this->assertSame([2, 3], sequence([blocking(fn () => 2), blocking(fn () => 3)])->unsafeRun());
    }

    #[Test]
    public function traverse_maps_each_value_to_an_effect_and_sequences_them()
    {
        $this->assertSame([2, 4, 6], traverse([1, 2, 3], fn (int $n) => io(fn () => $n * 2))->unsafeRun());
    }
}
