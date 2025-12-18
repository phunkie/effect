<?php

namespace Tests\Unit\Phunkie\Effect\IO\IOApp;

use PHPUnit\Framework\TestCase;

use function Phunkie\Effect\Functions\ioapp\arguments;

use const Phunkie\Effect\Functions\ioapp\Negatable;
use const Phunkie\Effect\Functions\ioapp\NoInput;

use function Phunkie\Effect\Functions\ioapp\option;

use const Phunkie\Effect\Functions\ioapp\Optional;
use const Phunkie\Effect\Functions\ioapp\Required;

use Phunkie\Effect\IO\IOApp\Options;

class OptionsTest extends TestCase
{
    public function test_it_creates_options_with_mixed_definitions()
    {
        $validation = arguments(
            option('h', 'help', 'Show help', Optional),
            option('n', 'name', 'Name given', Required),
            option('v', 'verbose', 'Verbose mode', NoInput)
        );

        $this->assertTrue($validation->isRight());
        $this->assertInstanceOf(Options::class, $validation->toOption()->get());
    }

    public function test_it_parses_short_flags()
    {
        $options = arguments(
            option('v', 'verbose', 'Verbose', NoInput),
            option('d', 'debug', 'Debug', NoInput)
        )->toOption()->get();

        $parsed = $options->parse(['-v', '-d'])->getOrElse(null);

        $this->assertTrue($parsed->has('verbose'));
        $this->assertTrue($parsed->has('debug'));
    }

    public function test_it_parses_bundled_short_flags()
    {
        $options = arguments(
            option('a', 'all', '', NoInput),
            option('l', 'long', '', NoInput)
        )->toOption()->get();

        $parsed = $options->parse(['-al'])->getOrElse(null);

        $this->assertTrue($parsed->has('all'));
        $this->assertTrue($parsed->has('long'));
    }

    public function test_it_parses_required_values()
    {
        $options = arguments(
            option('f', 'file', 'File', Required)
        )->toOption()->get();

        $parsed = $options->parse(['-f', 'test.txt'])->getOrElse(null);

        $this->assertTrue($parsed->has('file'));
        $this->assertEquals('test.txt', $parsed->fetch('file')->get()->value);
    }

    public function test_it_parses_bundled_value_in_short_flag()
    {
        $options = arguments(
            option('p', 'port', 'Port', Required)
        )->toOption()->get();

        $parsed = $options->parse(['-p8080'])->getOrElse(null);

        $this->assertEquals('8080', $parsed->fetch('port')->get()->value);
    }

    public function test_it_parses_long_flags_with_equals()
    {
        $options = arguments(
            option('n', 'name', 'Name', Required)
        )->toOption()->get();

        $parsed = $options->parse(['--name=John'])->getOrElse(null);

        $this->assertEquals('John', $parsed->fetch('name')->get()->value);
    }

    public function test_it_parses_negatable_options()
    {
        $options = arguments(
            option('c', 'color', 'Enable color', Negatable)
        )->toOption()->get();

        $parsedEnable = $options->parse(['--color'])->getOrElse(null);
        $this->assertEquals(true, $parsedEnable->fetch('color')->get()->value);

        $parsedDisable = $options->parse(['--no-color'])->getOrElse(null);
        $this->assertEquals(false, $parsedDisable->fetch('color')->get()->value);
    }

    public function test_dsl_polymorphic_add()
    {
        $options = arguments(
            option('h', 'help', 'Help'), // 3 args standard
            option('verify', 'Verify', Negatable), // 3 args, 3rd is format
            option('silent', 'Be silent') // 2 args
        )->toOption()->get();

        // Test verify logic
        $parsed = $options->parse(['--no-verify'])->getOrElse(null);
        $this->assertEquals(false, $parsed->fetch('verify')->get()->value);

        // Test silent logic (Optional default)
        $parsedSilent = $options->parse(['--silent'])->getOrElse(null);
        $this->assertEquals(true, $parsedSilent->fetch('silent')->get()->value);
    }
}
