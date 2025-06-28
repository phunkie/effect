<?php

namespace Phunkie\Effect\Tests\IO;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\io\bracket;

class BracketTest extends TestCase
{
    #[Test]
    public function it_acquires_uses_and_releases_resource()
    {
        $released = false;
        $handle = null;

        $acquireResource = io(function() use (&$handle) {
            $handle = fopen('php://memory', 'r+');
            fwrite($handle, 'test');
            rewind($handle);
            return $handle;
        });
        $useResource = function($resource) {
            return io(function() use ($resource) {
                return fread($resource, 4);
            });
        };
        $releaseResource = function($resource) use (&$released) {
            return io(function() use ($resource, &$released) {
                fclose($resource);
                $released = true;
            });
        };

        $result = bracket(
            $acquireResource,
            $useResource,
            $releaseResource
        )->unsafeRun();

        $this->assertEquals('test', $result);
        $this->assertTrue($released, 'Resource was not released');
        $this->assertFalse(is_resource($handle), 'Resource handle should be closed');
    }

    #[Test]
    public function it_calls_release_on_use_error()
    {
        $released = false;
        $handle = null;
        $errorMessage = null;

        $acquireResource = io(function() use (&$handle) {
            $handle = fopen('php://memory', 'r+');
            return $handle;
        });
        $useResource = function($resource) {
            return io(function() {
                throw new \RuntimeException('use failed');
            });
        };
        $releaseResource = function($resource) use (&$released) {
            return io(function() use ($resource, &$released) {
                fclose($resource);
                $released = true;
            });
        };

        bracket(
            $acquireResource,
            $useResource,
            $releaseResource
        )
        ->handleError(function($error) use (&$errorMessage) {
            $errorMessage = $error->getMessage();
        })
        ->unsafeRun();

        $this->assertEquals('use failed', $errorMessage, 'Error was not caught');
        $this->assertTrue($released, 'Resource was not released on use error');
        $this->assertFalse(is_resource($handle), 'Resource handle should be closed on use error');
    }
} 
