<?php

namespace Tests\Ops\IO;

use Phunkie\Effect\IO\IO;
use Phunkie\Cats\Functor;
use Phunkie\Types\Unit;
use Phunkie\Types\Pair;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FunctorOpsTest extends TestCase
{
    #[Test]
    public function it_maps_over_io_values()
    {
        $io = new IO(function () {
            return 42;
        });

        $mapped = $io->map(function ($x) {
            return $x * 2;
        });

        $this->assertEquals(84, $mapped->unsafeRun());
    }

    #[Test]
    public function it_implements_phunkie_functor_interface()
    {
        $this->assertInstanceOf(Functor::class, new IO(function () {
            return 42;
        }));
    }

    #[Test]
    public function it_lifts_functions_to_operate_on_io()
    {
        $io = new IO(function () {
            return 42;
        });

        $lifted = $io->lift(function ($x) {
            return $x * 2;
        });

        $this->assertEquals(84, $lifted($io)->unsafeRun());
    }

    #[Test]
    public function it_replaces_io_contents_with_constant_value()
    {
        $io = new IO(function () {
            return 42;
        });

        $replaced = $io->as("x");

        $this->assertEquals("x", $replaced->unsafeRun());
    }

    #[Test]
    public function it_discards_io_contents_replacing_with_unit()
    {
        $io = new IO(function () {
            return 42;
        });

        $voided = $io->void();

        $this->assertInstanceOf(Unit::class, $voided->unsafeRun());
    }

    #[Test]
    public function it_zips_io_values_with_mapped_values()
    {
        $io = new IO(function () {
            return 42;
        });

        $zipped = $io->zipWith(function ($x) {
            return $x * 2;
        });

        $result = $zipped->unsafeRun();
        $this->assertInstanceOf(Pair::class, $result);
        $this->assertEquals(42, $result->_1);
        $this->assertEquals(84, $result->_2);
    }

    #[Test]
    public function it_performs_invariant_mapping()
    {
        $io = new IO(function () {
            return 42;
        });

        $imapped = $io->imap(
            function ($x) {
                return $x * 2;
            },  // forward
            function ($x) {
                return $x / 2;
            }   // reverse
        );

        $this->assertEquals(84, $imapped->unsafeRun());
    }
}
