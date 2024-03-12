<?php

namespace Lunar\BulBank;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Models\Order;
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

        if (!$this->order) {
            try {
                $this->order = $this->cart->createOrder();
            } catch (DisallowMultipleCartOrdersException $e) {
                return new PaymentAuthorize(
                    success: false,
                    message: $e->getMessage(),
                );
            }
        }
        $this->storeTransaction(
            transaction: $this->data['responseData'],
            success: 'Ok'
        );

        $this->order->update([
            'status' => 'payment-received',
            'placed_at' => now(),
        ]);


        return new PaymentAuthorize(
            success: (bool)$this->order->placed_at,
            message: 'bulbank payment',
            orderId: $this->order->id
        );
    }

    /**
     * @param Transaction $transaction
     * @param int $amount
     * @param $notes
     * @return PaymentRefund
     */
    public function refund(Transaction $transaction, int $amount, $notes = null): PaymentRefund
    {
        return new PaymentRefund(
            success: true
        );
    }

    /**
     * @param Transaction $transaction
     * @param $amount
     * @return PaymentCapture
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {

        return new PaymentCapture(success: true);
    }

    /**
     * @param $transaction
     * @param bool $success
     * @return void
     */
    protected function storeTransaction($transaction, bool $success = false): void
    {
        $data = [
            'success' => $success,
            'type' => 'capture',
            'driver' => 'bulbank',
            'amount' => $transaction['AMOUNT'],
            'status' => $transaction['STATUSMSG'],
            'notes' => $transaction['TERMINAL'],
            'card_type' => $transaction['CARD_BRAND'],
            'last_four' => $transaction['CARD'],
            'captured_at' =>  now() ,
            'meta' => [
                'bul_bank_info' => [
                    'approval' =>$transaction['APPROVAL'],
                    'rrn' =>$transaction['RRN'],
                    'int_ref' =>$transaction['INT_REF'],
                    'card' =>$transaction['CARD'],
                    'eci' =>$transaction['ECI'],
                    'nonce' =>$transaction['NONCE'],

                ],
            ],
        ];
        $this->order->transactions()->create($data);
    }
}
