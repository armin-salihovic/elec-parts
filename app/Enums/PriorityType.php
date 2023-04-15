<?php

namespace App\Enums;

abstract class PriorityType
{
    const FIFO = 0;
    const LIFO = 1;
    const SmallestStock = 2;
    const LargestStock = 3;
}
