<?php

namespace Phunkie\Effect\Concurrent;

interface AsyncHandle
{
    public function await(): mixed;
} 
