<?php

namespace Phunkie\Effect\Cats;

interface Parallel
{
    public function parMap2(Parallel $fb, callable $f): Parallel;
}
