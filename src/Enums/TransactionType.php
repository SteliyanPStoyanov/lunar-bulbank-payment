<?php
namespace Lunar\BulBank\Enums;

use MyCLabs\Enum\Enum;

/**
 * Class TransactionType
 * @method static SALE()
 * @method static TRANSACTION_STATUS_CHECK()
 * @method static REVERSAL()
 * @method static PRE_AUTHORISATION()
 * @method static PRE_AUTHORISATION_COMPLETION()
 * @method static PRE_AUTHORISATION_REVERSAL()
 */
class TransactionType extends Enum
{
    const SALE = 1;
    const PRE_AUTHORISATION = 12;
    const PRE_AUTHORISATION_COMPLETION = 21;
    const PRE_AUTHORISATION_REVERSAL = 22;
    const TRANSACTION_STATUS_CHECK = 90;
    const REVERSAL = 24;
}
