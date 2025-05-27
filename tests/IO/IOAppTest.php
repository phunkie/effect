<?php

namespace Tests\Phunkie\Effect\IO;

use Phunkie\Effect\IO\IO;
use Phunkie\Effect\IO\IOApp;
use PHPUnit\Framework\TestCase;
use function Phunkie\Effect\Functions\io\io;
use const Phunkie\Effect\IOApp\ExitSuccess;
use const Phunkie\Effect\IOApp\ExitFailure;

class TestApp extends IOApp
{
    public function run(): IO
    {
        return io(function() {
            return ExitSuccess;
        });
    }
}

class FailingApp extends IOApp
{
    public function run(): IO
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
    public function testSuccessfulAppReturnsExitSuccess(): void
    {
        $app = new TestApp();
        $result = $app->run()->unsafeRun();
        $this->assertEquals(ExitSuccess, $result);
    }

    public function testFailingAppReturnsExitFailure(): void
    {
        $app = new FailingApp();
        $result = $app->run()->unsafeRun();
        $this->assertEquals(ExitFailure, $result);
    }
} 
