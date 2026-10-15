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

use function Phunkie\Effect\Functions\io\io;

use Phunkie\Effect\IO\Resource;
use RuntimeException;

class ResourceTest extends TestCase
{
    private array $log = [];

    #[Test]
    public function it_acquires_for_the_use_step_and_releases_after_it()
    {
        $result = $this->resource('db')->use(fn (string $db) => $this->logged("query $db", 'rows'))->unsafeRun();

        $this->assertSame('rows', $result);
        $this->assertSame(['open db', 'query db', 'close db'], $this->log);
    }

    #[Test]
    public function it_releases_when_the_use_step_throws_and_rethrows()
    {
        try {
            $this->resource('db')->use(fn () => io(fn () => throw new RuntimeException('boom')))->unsafeRun();
            $this->fail('Expected the error to propagate.');
        } catch (RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $this->assertSame(['open db', 'close db'], $this->log);
    }

    #[Test]
    public function it_nests_with_flat_map_and_releases_the_inner_one_first()
    {
        $both = $this->resource('db')->flatMap(fn (string $db) => $this->resource("cache for $db"));

        $both->use(fn (string $cache) => $this->logged("use $cache", null))->unsafeRun();

        $this->assertSame(['open db', 'open cache for db', 'use cache for db', 'close cache for db', 'close db'], $this->log);
    }

    #[Test]
    public function it_maps_what_was_acquired()
    {
        $this->assertSame('DB', $this->resource('db')->map(strtoupper(...))->use(fn (string $name) => io(fn () => $name))->unsafeRun());
    }

    #[Test]
    public function it_lifts_a_value_and_an_io_as_resources_that_need_no_release()
    {
        $this->assertSame(3, Resource::pure(1)->flatMap(fn (int $a) => Resource::eval(io(fn () => $a + 2)))->use(fn (int $b) => io(fn () => $b))->unsafeRun());
    }

    #[Test]
    public function nothing_is_acquired_until_the_io_runs()
    {
        $this->resource('db')->use(fn () => io(fn () => null));

        $this->assertSame([], $this->log);
    }

    private function resource(string $name): Resource
    {
        return Resource::make($this->logged("open $name", $name), fn (string $acquired) => $this->logged("close $acquired", null));
    }

    private function logged(string $entry, mixed $value)
    {
        return io(function () use ($entry, $value) {
            $this->log[] = $entry;

            return $value;
        });
    }
}
