<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Cats;

/**
 * Interface representing a computation that can be run in parallel.
 *
 * This interface corresponds to Cats' Parallel type class, allowing
 * conversion between sequential (Monad) and parallel (Applicative) execution modes.
 *
 * @template A
 */
interface Parallel
{
    /**
     * Combines this parallel computation with another using a combining function.
     *
     * @template B
     * @template C
     * @param Parallel<B> $fb The computation to combine with
     * @param callable(A, B): C $f The function to combine the results
     * @return Parallel<C> The result of combined computation
     */
    public function parMap2(Parallel $fb, callable $f): Parallel;

    /**
     * Returns the underlying unsafe runner.
     *
     * @return callable(): A
     */
    public function getUnsafeRun(): callable;
}
