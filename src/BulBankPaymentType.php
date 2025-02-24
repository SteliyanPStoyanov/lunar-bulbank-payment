<?php

namespace Lunar\BulBank;

use Illuminate\Support\Facades\Log;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\BulBank\DataTransferObjects\PaymentCancel;
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

        $saleRequest = (new SaleRequest())
            ->setSigningSchemaMacGeneral()
            ->setEnvironment(config('bulbank.environment'))
            ->setAmount($this->order->total->value / 100)
            ->setMerchantName('ABC PFARMACY LTD')
            ->setMerchantGMT('+03')
            ->setCountryCode('BG')
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
            ->setPrivateKeyPassword(config('bulbank.private_key_pass'));

        $response = $saleRequest->send();

        Log::info(json_encode($response->isSuccessful()) . json_encode($response->getResponseData(false)));

        if ($this->order->transactions()->count() === 0) {
            $this->storeTransaction(
                transaction: $response->getResponseData(false),
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
            ->setSigningSchemaMacGeneral()
            ->setEnvironment(config('bulbank.environment'))
            ->setPublicKey(base_path(config('bulbank.public_cer_path')))
            ->setAmount($amount / 100)
            ->setCurrency('BGN')
            ->setOrder($transaction->order_id)
            ->setDescription(!empty($notes) ? $notes : 'Детайли плащане.')
            ->setTerminalID(config('bulbank.terminal_id'))
            ->setMerchantId(config('bulbank.merchant_id'))
            ->setMerchantName('ABC PFARMACY LTD')
            ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
            ->setPrivateKeyPassword(config('bulbank.private_key_pass'))
            ->setIntRef($transaction->meta['bul_bank_info']['int_ref'])
            ->setRrn($transaction->meta['bul_bank_info']['rrn'])
            ->setNonce($transaction->meta['bul_bank_info']['nonce']);

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
