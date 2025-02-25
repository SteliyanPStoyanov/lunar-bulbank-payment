<?php

namespace Lunar\BulBank;

use Illuminate\Support\Facades\Log;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\BulBank\DataTransferObjects\PaymentCancel;
use Lunar\BulBank\DataTransferObjects\PaymentInit;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Models\Customer;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\BulBank\Services\ReversalRequest;
use Lunar\BulBank\Services\SaleRequest;

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

    public function init(): PaymentInit
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

        $saleRequest = (new SaleRequest())
            ->setSigningSchemaMacGeneral()
            ->setEnvironment(config('bulbank.environment'))
            ->setAmount($this->order->total->value / 100)
            ->setMerchantName('ABC PFARMACY LTD')
            ->setMerchantGMT('+03')
            ->setCountryCode('BG')
            ->setLang('BG')
            ->setMInfo(array(
                'email' => $this->data['email'],
                'cardholderName' => $this->data['cardholderName'],
                'mobilePhone' => array(
                    'cc' => '359',
                    'subscriber' => $this->data['subscriber'],
                )
            ))
            ->setOrder($this->order->id)
            ->setDescription('order #' . $this->order->id)
            ->setMerchantUrl(url('/')) // optional
            ->setTerminalID(config('bulbank.terminal_id'))
            ->setMerchantId(config('bulbank.merchant_id'))
            ->setEmailAddress('contact@abcpharmacy.bg')
            ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
            ->setPrivateKeyPassword(config('bulbank.private_key_pass'))
            ->setAddCustomBoricaOrderId($this->order->id);

        $this->order->update([
            'status' => 'awaiting-payment',
            'placed_at' => now(),
        ]);

        return new PaymentInit(
            success: (bool)$this->order->placed_at,
            form: $saleRequest->generateForm(),
            orderId: $this->order->id
        );
    }

    public function authorize(): PaymentAuthorize
    {
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

        if ($this->order->transactions()->count() === 0) {
            $this->storeTransaction(
                transaction: $this->data['responseData'],
                success: 'Ok'
            );
        }

        $this->order->update([
            'status' => 'payment-received',
            'placed_at' => now(),
        ]);


        return new PaymentAuthorize(
            success: (bool)$this->order->placed_at,
            message: 'BulBank payment received',
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
       $reversal = (new ReversalRequest())
            ->setTerminalID(config('bulbank.terminal_id'))
            ->setAmount($amount / 100)
            ->setCurrency('BGN')
            ->setOrder($transaction->order_id)
            ->setDescription(!empty($notes) ? $notes : 'Детайли плащане.')
            ->setMerchantId(config('bulbank.merchant_id'))
            ->setMerchantName('ABC PFARMACY LTD')
            ->setMerchantUrl(url('/'))
            ->setEmailAddress('contact@abcpharmacy.bg')
            ->setCountryCode('BG')
            ->setMerchantGMT('+03')
            ->setLang('BG')
            ->setRrn($transaction->meta['bul_bank_info']['rrn'])
            ->setIntRef($transaction->meta['bul_bank_info']['int_ref'])
            ->setSigningSchemaMacGeneral()
            ->setEnvironment(config('bulbank.environment'))
            ->setPublicKey(base_path(config('bulbank.public_cer_path')))
            ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
            ->setPrivateKeyPassword(config('bulbank.private_key_pass'))
            ->setNonce($transaction->meta['bul_bank_info']['nonce'])
            ->setAddCustomBoricaOrderId($transaction->order_id);

        $reversal->send();

        return new PaymentRefund(
            success: true,
            message: 'BulBank payment refund',
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
            'amount' => $transaction['AMOUNT'] * 100,
            'reference' => $transaction['ORDER'],
            'status' => $transaction['STATUSMSG'],
            'notes' => $transaction['TERMINAL'],
            'card_type' => $transaction['CARD_BRAND'],
            'last_four' => $transaction['CARD'],
            'captured_at' => now(),
            'meta' => [
                'bul_bank_info' => [
                    'approval' => $transaction['APPROVAL'],
                    'rrn' => $transaction['RRN'],
                    'int_ref' => $transaction['INT_REF'],
                    'card' => $transaction['CARD'],
                    'eci' => $transaction['ECI'],
                    'nonce' => $transaction['NONCE'],

                ],
            ],
        ];
        $this->order->transactions()->create($data);
    }

    public function cancel(): PaymentCancel
    {
        $this->order = $this->cart->draftOrder ?: $this->cart->completedOrder;

        if (!$this->order) {
            try {
                $this->order = $this->cart->createOrder();
            } catch (DisallowMultipleCartOrdersException $e) {
                return new PaymentCancel(
                    success: false,
                    message: $e->getMessage(),
                );
            }
        }
        $response = $this->data['responseData'];
        $this->order->update([
            'status' => 'payment-cancel',
            'placed_at' => now(),
            'meta' => [
                "RC" => $response['RC'],
                'status' => $response['STATUSMSG'],
                'nonce' => $response['NONCE'],
            ]
        ]);

        $this->cart->update([
            'order_id' => $this->order->id
        ]);


        return new PaymentCancel(
            success: (bool)$this->order->placed_at,
            message: 'BulBank payment cancel',
            orderId: $this->order->id
        );
    }
}
