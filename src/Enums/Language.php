<?php

namespace Lunar\BulBank\Enums;

use MyCLabs\Enum\Enum;

/**
 * Class Language
 * @method static BG()
 * @method static EN()
 */
class Language extends Enum
{
    const BG = 'BG';
    const EN = 'EN';

    public static function isValid(string $lang): bool
    {
        return in_array($lang, [self::BG, self::EN], true);

    }
}
