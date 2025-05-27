<?php

namespace Phunkie\Effect\Concurrent;

class Blocking
{
    public function __construct(
        private readonly \Closure $thunk,
        private ?ExecutionContext $context = null
    ) {
        if ($this->context === null) {
            $this->context = new FiberExecutionContext();
        }
    }

    public function __invoke(): AsyncHandle
    {
        return $this->context->executeAsync($this->thunk);
    }
    
    public function runSync(): mixed
    {
        return $this->context?->execute($this->thunk);        
    }


} 
