<?php

namespace Lunar\BulBank\Models;

use MyCLabs\Enum\Enum;

/**
 * Class TransactionType *
 * @method static SALE()
 * @method static TRANSACTION_STATUS_CHECK()
 * @method static REVERSAL()
 * @method static REVERSAL_REQUEST()
 * @method static REVERSAL_REQUESTREVERSAL_REQUEST()
 * @method static DEFERRED_AUTHORIZATION()
 */
class TransactionType extends Enum
{
    const SALE = 1;
    const DEFERRED_AUTHORIZATION = 12;
    const COMPLETION_DEFERRED_AUTHORIZATION = 21;
    const REVERSAL_REQUEST = 22;
    const TRANSACTION_STATUS_CHECK = 90;
    const REVERSAL = 24;
}
