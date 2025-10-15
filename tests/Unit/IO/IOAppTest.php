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

use Phunkie\Effect\IO\IO;
use Phunkie\Effect\IO\IOApp;

use const Phunkie\Effect\IOApp\ExitFailure;
use const Phunkie\Effect\IOApp\ExitSuccess;

class TestApp extends IOApp
{
    public function run(?array $args = []): IO
    {
        return io(function () {
            return ExitSuccess;
        });
    }
}

class FailingApp extends IOApp
{
    public function run(?array $args = []): IO
    {
        return io(function () {
            throw new \Exception("Test error");
        })->handleError(function ($error) {
            return ExitFailure;
        });
    }
}

class IOAppTest extends TestCase
{
    #[Test]
    public function it_returns_exit_success_for_successful_app(): void
    {
        $app = new TestApp();
        $result = $app->run()->unsafeRun();
        $this->assertEquals(ExitSuccess, $result);
    }

    #[Test]
    public function it_returns_exit_failure_for_failing_app(): void
    {
        $app = new FailingApp();
        $result = $app->run()->unsafeRun();
        $this->assertEquals(ExitFailure, $result);
    }
}
