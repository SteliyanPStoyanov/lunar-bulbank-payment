<?php

namespace Lunar\BulBank;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;

/**
 * Class TransactionType
 * @method static SALE()
 * @method static TRANSACTION_STATUS_CHECK()
 * @method static REVERSAL()
 * @method static REVERSAL_REQUEST()
 * @method static REVERSAL_REQUESTREVERSAL_REQUEST()
 * @method static DEFERRED_AUTHORIZATION()
 */
class BulBankPaymentType extends AbstractPayment
{

    public function authorize(): PaymentAuthorize
    {
         $this->order = $this->cart->draftOrder ?: $this->cart->completedOrder;

        if (! $this->order) {
            try {
                $this->order = $this->cart->createOrder();
            } catch (DisallowMultipleCartOrdersException $e) {
                return new PaymentAuthorize(
                    success: false,
                    message: $e->getMessage(),
                );
            }
        }
         return new PaymentAuthorize(
            success: (bool) $this->order->placed_at,
            message: 'ххххххх',
            orderId: $this->order->id
        );
    }

    public function refund(Transaction $transaction, int $amount, $notes = null): PaymentRefund
    {
       return new PaymentRefund(
            success: true
        );
    }

    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
          $payload = [];

        if ($amount > 0) {
            $payload['amount_to_capture'] = $amount;
        }
         return new PaymentCapture(success: true);
    }
}
