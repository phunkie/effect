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

/**
 * Defines a command line option.
 */
class OptionDefinition
{
    public readonly ?string $short;
    public readonly ?string $long;
    public readonly string $description;
    public readonly OptionFormat $format;

    public function __construct(string $p1, string|null $p2 = null, string|OptionFormat|null $p3 = null, OptionFormat $p4 = OptionFormat::Optional)
    {
        $short = null;
        $long = null;
        $description = '';
        $format = OptionFormat::Optional;

        if ($p3 instanceof OptionFormat) {
            $format = $p3;
            $description = $p2 ?? '';

            if (strlen($p1) === 1) {
                $short = $p1;
            } else {
                $long = $p1;
            }
        } elseif (is_string($p3)) {
            $short = $p1;
            $long = $p2;
            $description = $p3;
            $format = $p4;
        } else {
            if ($p2 !== null && str_contains($p2, ' ')) {
                $description = $p2;
                if (strlen($p1) === 1) {
                    $short = $p1;
                } else {
                    $long = $p1;
                }
            } else {
                $short = $p1;
                $long = $p2;
            }
        }

        // Emptiness rather than absence: the name is a required string and every
        // branch above assigns it to one of the two, so neither can be null and
        // a test for that never fired. What it meant to catch is an option no
        // one could ever pass.
        if (($short ?? '') === '' && ($long ?? '') === '') {
            throw new \InvalidArgumentException('At least one of short or long name must be provided.');
        }

        $this->short = $short;
        $this->long = $long;
        $this->description = $description;
        $this->format = $format;
    }

    public function describe(): string
    {
        $parts = [];
        if ($this->short) {
            $parts[] = "-" . $this->short;
        }
        if ($this->long) {
            $parts[] = "--" . $this->long;
        }
        $flags = implode(', ', $parts);
        if ($this->format === OptionFormat::Required || $this->format === OptionFormat::Optional) {
            $flags .= " <value>";
        }

        return sprintf("  %-25s %s", $flags, $this->description);
    }
}
