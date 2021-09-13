<?php

declare(strict_types=1);

namespace NotZ\utils\math;

class Time {

    public static function calculateTime(int $time): string {
        return gmdate("i:s", $time); 
    }
}
