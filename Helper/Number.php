<?php

namespace Billink\Billink\Helper;

use function abs;

class Number
{
    public function floatsAreEqual(float $number1, float $number2): bool
    {
        if ($number2 == 0.00) {
            return false;
        }

        return (abs(($number1 - $number2) / $number2) < 0.00001);
    }
}
