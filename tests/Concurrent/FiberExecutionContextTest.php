<?php

namespace Tests\Phunkie\Effect\Concurrent;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Phunkie\Effect\Concurrent\FiberExecutionContext;

class FiberExecutionContextTest extends TestCase
{
    #[Test]
    public function it_runs_thunks_in_a_fiber()
    {
        $context = new FiberExecutionContext();
        $thunk = function() { return 42; };

        $result = $context->execute($thunk);

        $this->assertEquals(42, $result);
    }

    //#[Test]
    public function it_preserves_fiber_state()
    {
        $context = new FiberExecutionContext();
        $counter = 0;
        
        $thunk = function() use (&$counter) {
            $counter++;
            return $counter;
        };

        $result1 = $context->execute($thunk);
        $result2 = $context->execute($thunk);

        $this->assertEquals(1, $result1);
        $this->assertEquals(2, $result2);
    }
} 
