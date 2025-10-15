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
use Phunkie\Cats\Monad;
use Phunkie\Effect\IO\IO;

class MonadOpsTest extends TestCase
{
    #[Test]
    public function it_implements_phunkie_monad_interface()
    {
        $this->assertInstanceOf(Monad::class, new IO(function () {
            return 42;
        }));
    }

    #[Test]
    public function it_flatmaps_over_io_values()
    {
        $io = new IO(function () {
            return 42;
        });

        $result = $io->flatMap(function ($x) {
            return new IO(function () use ($x) {
                return $x * 2;
            });
        });

        $this->assertEquals(84, $result->unsafeRun());
    }

    #[Test]
    public function it_flattens_nested_io_values()
    {
        $nested = new IO(function () {
            return new IO(function () {
                return 42;
            });
        });

        $this->assertEquals(42, $nested->flatten()->unsafeRun());
    }

    #[Test]
    public function it_follows_monad_left_identity_law()
    {
        $f = function ($x) {
            return new IO(function () use ($x) {
                return $x * 2;
            });
        };

        $io = new IO(function () {
            return null;
        });
        $left = $io->pure(21)->flatMap($f);
        $right = $f(21);

        $this->assertEquals(
            $left->unsafeRun(),
            $right->unsafeRun()
        );
    }

    #[Test]
    public function it_follows_monad_right_identity_law()
    {
        $io = new IO(function () {
            return 42;
        });

        $this->assertEquals(
            $io->unsafeRun(),
            $io->flatMap(function ($x) {
                return new IO(function () use ($x) {
                    return $x;
                });
            })->unsafeRun()
        );
    }

    #[Test]
    public function it_follows_monad_associativity_law()
    {
        $io = new IO(function () {
            return 21;
        });

        $f = function ($x) {
            return new IO(function () use ($x) {
                return $x * 2;
            });
        };

        $g = function ($x) {
            return new IO(function () use ($x) {
                return $x + 1;
            });
        };

        $left = $io->flatMap($f)->flatMap($g);
        $right = $io->flatMap(function ($x) use ($f, $g) {
            return $f($x)->flatMap($g);
        });

        $this->assertEquals(
            $left->unsafeRun(),
            $right->unsafeRun()
        );
    }
}
