<?php

namespace Tests\Phunkie\Effect\IO;

use Phunkie\Effect\IO\IO;
use Phunkie\Effect\IO\IOApp;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use function Phunkie\Effect\Functions\io\io;
use const Phunkie\Effect\IOApp\ExitSuccess;
use const Phunkie\Effect\IOApp\ExitFailure;

class TestApp extends IOApp
{
    public function run(?array $args = []): IO
    {
        return io(function() {
            return ExitSuccess;
        });
    }
}

class FailingApp extends IOApp
{
    public function run(?array $args = []): IO
    {
        return io(function() {
            throw new \Exception("Test error");
        })->handleError(function($error) {
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
