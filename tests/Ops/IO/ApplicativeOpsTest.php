<?php

namespace Tests\Ops\IO;

use Phunkie\Effect\IO\IO;
use Phunkie\Cats\Applicative;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ApplicativeOpsTest extends TestCase
{
    #[Test]
    public function it_implements_phunkie_applicative_interface()
    {
        $this->assertInstanceOf(Applicative::class, new IO(function () {
            return 42;
        }));
    }

    #[Test]
    public function it_lifts_pure_values_into_io()
    {
        $io = (new IO(function () {
            return null;
        }))->pure(42);
        $this->assertEquals(42, $io->unsafeRun());
    }

    #[Test]
    public function it_applies_wrapped_functions_to_io_values_corrected()
    {
        $io_value = new IO(function () {
            return 42;
        }); // This is the value IO: IO<int>

        $io_function = new IO(function () {
            return function ($x) {
                return $x * 2;
            };
        }); // This is the function IO: IO<callable(int):int>

        // Correct call: function IO calls apply on value IO
        $result = $io_function->apply($io_value);

        $this->assertEquals(84, $result->unsafeRun());
    }

    #[Test]
    public function it_performs_map2_on_two_io_instances()
    {

        $io1 = new IO(function () {
            return 2;
        });

        $io2 = new IO(function () {
            return 3;
        });

        $result = $io1->map2($io2, function ($a, $b) {
            return $a * $b;
        });

        $this->assertEquals(6, $result->unsafeRun());
    }

    #[Test]
    public function it_follows_applicative_identity_law()
    {
        $io = new IO(function () {
            return 42;
        });

        $id = function ($x) {
            return $x;
        };
        $appId = (new IO(function () {
            return null;
        }))->pure($id);

        $this->assertEquals(
            $io->unsafeRun(),
            $appId->apply($io)->unsafeRun()
        );
    }

    #[Test]
    public function it_follows_applicative_homomorphism_law()
    {
        $f = function ($x) {
            return $x * 2;
        };
        $x = 21;

        $io = new IO(function () {
            return null;
        });
        $left = $io->pure($f)->apply($io->pure($x));
        $right = $io->pure($f($x));

        $this->assertEquals(
            $left->unsafeRun(),
            $right->unsafeRun()
        );
    }

    #[Test]
    public function it_follows_applicative_interchange_law()
    {
        // Define a simple multiplication function
        $f = function ($x) {
            return $x * 2;
        };
        $y = 21;

        /** @var IO<callable(int):int> $u */
        $u = new IO(function () use ($f) {
            return $f;
        });

        // Left side: u <*> pure(y)
        // This should apply the function in u to the value y
        $left = $u->apply($u->pure($y));

        // Right side: pure($ y) <*> u
        // This applies a function that feeds y to whatever function it receives
        $right = $u->pure(function ($g) use ($y) {
            return call_user_func($g, $y);
        })->apply($u);

        $this->assertEquals(
            $left->unsafeRun(),
            $right->unsafeRun()
        );
    }
}
