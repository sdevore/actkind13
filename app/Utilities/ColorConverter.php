<?php

namespace App\Utilities;

use Spatie\Color\Rgb;

class ColorConverter
{
    public static function toHexString(array $color, $intensity = 500): string
    {
        $string = $color[(string) $intensity];

        return (string) Rgb::fromString('rgb('.$string.')');
    }
}
