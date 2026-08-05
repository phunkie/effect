<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Phunkie\Effect\PatternMatching;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Phunkie\Effect\IO\IO;

use function Phunkie\Effect\PatternMatching\Referenced\IO as IOPattern;

class ReferencedIOTest extends TestCase
{
    #[Test]
    public function it_binds_the_thunk_when_matching_an_io()
    {
        $on = pmatch(new IO(fn () => 42));

        $result = match (true) {
            $on(IOPattern($thunk)) => $thunk()
        };

        $this->assertEquals(42, $result);
    }

    #[Test]
    public function it_does_not_match_a_value_that_is_not_an_io()
    {
        $on = pmatch(42);

        $result = match (true) {
            $on(IOPattern($thunk)) => "io",
            $on(_) => "not an io"
        };

        $this->assertEquals("not an io", $result);
    }
}
