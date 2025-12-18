<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\IO\IOApp;

use function Failure;
use function Nel;

use Phunkie\Validation\Validation;

use function Success;

/**
 * Registry of option definitions and the main parser.
 */
class Options
{
    private $definitions = [];

    private function __construct(array $definitions = [])
    {
        $this->definitions = $definitions;
    }

    public static function create(OptionDefinition ...$definitions): self
    {
        $hasHelp = false;
        $hasVersion = false;
        foreach ($definitions as $def) {
            if ($def->short === 'h' || $def->long === 'help') {
                $hasHelp = true;
            }
            if ($def->short === 'v' || $def->long === 'version') {
                $hasVersion = true;
            }
        }

        if (! $hasHelp) {
            $definitions[] = new OptionDefinition('h', 'help', 'Display this help message', OptionFormat::NoInput);
        }

        if (! $hasVersion) {
            $definitions[] = new OptionDefinition('v', 'version', 'Display version', OptionFormat::NoInput);
        }

        return new self($definitions);
    }

    public function add(OptionDefinition $definition): self
    {
        $definitions = $this->definitions;
        $definitions[] = $definition;

        return new self($definitions);
    }

    public function remove(string $name): self
    {
        $definitions = array_values(array_filter($this->definitions, function (OptionDefinition $def) use ($name) {
            return $def->short !== $name && $def->long !== $name;
        }));

        return new self($definitions);
    }

    public function describe(): string
    {
        $output = "Options:\n";
        foreach ($this->definitions as $def) {
            $output .= $def->describe() . "\n";
        }

        return $output;
    }

    public function parse(array $args): Validation
    {
        if (isset($args[0]) && ! str_starts_with($args[0], '-')) {
            array_shift($args);
        }

        $parsed = [];
        $errors = [];
        $positionalArgs = [];
        $count = count($args);

        for ($i = 0; $i < $count; $i++) {
            $arg = $args[$i];

            if ($arg === '--') {
                for ($j = $i + 1; $j < $count; $j++) {
                    $positionalArgs[] = $args[$j];
                }

                break;
            }

            if (str_starts_with($arg, '--')) {
                $err = $this->parseLongOption($arg, $args, $i, $parsed);
                if ($err) {
                    $errors[] = $err;
                }

                continue;
            }

            if (str_starts_with($arg, '-') && strlen($arg) > 1) {
                $errs = $this->parseShortOptions($arg, $args, $i, $parsed);
                if ($errs) {
                    $errors = array_merge($errors, $errs);
                }

                continue;
            }

            $positionalArgs[] = $arg;
        }

        if (count($errors) > 0) {
            return Failure(Nel(...$errors));
        }

        return Success(new ParsedOptions($parsed, $positionalArgs));
    }

    private function parseLongOption(string $arg, array &$args, int &$i, array &$parsed): ?Error
    {
        $name = substr($arg, 2);
        $value = null;

        if (str_contains($name, '=')) {
            [$name, $value] = explode('=', $name, 2);
        }

        $def = $this->findDefByLong($name);
        if (! $def) {
            return new Error("Unknown option: --$name");
        }

        $key = $def->long ?? $def->short;
        $parsed[$key] = $this->resolveValue($def, $value, $args, $i, $name);

        return null;
    }

    private function parseShortOptions(string $arg, array &$args, int &$i, array &$parsed): array
    {
        $chars = str_split(substr($arg, 1));
        $len = count($chars);
        $errors = [];

        // Standard bundled short options parsing logic
        for ($j = 0; $j < $len; $j++) {
            $char = $chars[$j];
            $def = $this->findDefByShort($char);

            if (! $def) {
                $errors[] = new Error("Unknown option: -$char");

                continue;
            }

            $key = $def->long ?? $def->short;

            // Check if this option expects a value
            if ($def->format === OptionFormat::Required || $def->format === OptionFormat::Optional) {
                // If there are more chars in this group, they are the value
                if ($j + 1 < $len) {
                    $value = substr($arg, $j + 2);
                    $parsed[$key] = $this->resolveValue($def, substr($arg, $j + 2), $args, $i, $char);

                    break;
                } else {
                    $parsed[$key] = $this->resolveValue($def, null, $args, $i, $char);

                    break;
                }
            } else {
                $parsed[$key] = $this->resolveValue($def, null, $args, $i, $char);
            }
        }

        return $errors;
    }

    private function resolveValue(OptionDefinition $def, ?string $explicitValue, array &$args, int &$i, string $name): mixed
    {
        if ($def->format === OptionFormat::NoInput) {
            return true;
        }

        if ($def->format === OptionFormat::Negatable) {
            // If name starts with no-, return false. Else true.
            return ! str_starts_with($name, 'no-');
        }

        if ($def->format === OptionFormat::ArrayValues) {
            return [];
        }

        // Required or Optional
        if ($explicitValue !== null) {
            return $explicitValue;
        }

        // consume next arg
        if (isset($args[$i + 1]) && ! str_starts_with($args[$i + 1], '-')) {
            $i++;

            return $args[$i];
        }

        if ($def->format === OptionFormat::Required) {
            return null;
        }

        return true;
    }

    private function findDefByLong(string $name): ?OptionDefinition
    {
        foreach ($this->definitions as $def) {
            if ($def->long === $name) {
                return $def;
            }

            // Check for negatable --no-name match
            if ($def->format === OptionFormat::Negatable && str_starts_with($name, 'no-') && $def->long === substr($name, 3)) {
                return $def;
            }
        }

        return null;
    }

    private function findDefByShort(string $char): ?OptionDefinition
    {
        foreach ($this->definitions as $def) {
            if ($def->short === $char) {
                return $def;
            }
        }

        return null;
    }

    public function hasOption(string $query, array $args): bool
    {
        return $this->parse($args)->fold(
            fn ($errors) => false
        )(
            fn ($parsed) => $this->checkOption($query, $parsed)
        );
    }

    private function checkOption(string $query, ParsedOptions $parsed): bool
    {
        foreach ($this->definitions as $d) {
            if ($d->short === $query || $d->long === $query) {
                // Determine the key used in parsed
                $targetKey = $d->long ?? $d->short;

                return $parsed->has($targetKey);
            }
        }

        return false;
    }
}
