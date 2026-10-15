<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Concurrent;

use RuntimeException;

/**
 * Raised by timeout() when the effect has not finished within its time; the effect was cancelled.
 */
final class TimeoutException extends RuntimeException
{
}
