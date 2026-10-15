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

use function Phunkie\Effect\Functions\io\bracketCase;
use function Phunkie\Effect\Functions\io\bracketE;
use function Phunkie\Effect\Functions\io\io;

use Phunkie\Effect\IO\ExitCase;
use RuntimeException;
use Throwable;

class BracketCaseTest extends TestCase
{
    #[Test]
    public function bracket_case_tells_the_release_step_how_the_use_step_ended()
    {
        $cases = [];
        $release = function (string $resource, ExitCase $case) use (&$cases) {
            return io(function () use ($resource, $case, &$cases) {
                $cases[$resource] = $case;
            });
        };

        $result = bracketCase(io(fn () => 'ok'), fn () => io(fn () => 'done'), $release)->unsafeRun();

        try {
            bracketCase(io(fn () => 'bad'), fn () => io(fn () => throw new RuntimeException('boom')), $release)->unsafeRun();
            $this->fail('Expected the error to propagate.');
        } catch (RuntimeException $e) {
        }

        $this->assertSame('done', $result);
        $this->assertSame(['ok' => ExitCase::Completed, 'bad' => ExitCase::Error], $cases);
    }

    #[Test]
    public function bracket_e_hands_the_release_step_the_error_or_null()
    {
        $errors = [];
        $release = function (string $resource, ?Throwable $error) use (&$errors) {
            return io(function () use ($resource, $error, &$errors) {
                $errors[$resource] = $error?->getMessage();
            });
        };

        bracketE(io(fn () => 'ok'), fn () => io(fn () => 1), $release)->unsafeRun();

        try {
            bracketE(io(fn () => 'bad'), fn () => io(fn () => throw new RuntimeException('boom')), $release)->unsafeRun();
        } catch (RuntimeException $e) {
        }

        $this->assertSame(['ok' => null, 'bad' => 'boom'], $errors);
    }
}
