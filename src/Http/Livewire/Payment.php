<?php

namespace Lunar\BulBank\Http\Livewire;

use Livewire\Component;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Services\SaleRequest;
use Lunar\Models\Cart;

class Payment extends Component
{

    public Cart $cart;
    public $terms = false;
    public $boricaFormHtml = '';


    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('bulbank::livewire.payment');
    }

    /**
     * @throws SignatureException
     * @throws ParameterValidationException
     */
    public function send(): void
    {
        if ($this->terms === false) {
            $this->addError('terms', 'Полето а задължително !');
        } else {
            $saleRequest = (new SaleRequest())
                ->setSigningSchemaMacGeneral()
                ->inDevelopment() // set to development
                ->setAmount($this->cart->total->value / 100)
                ->setMerchantName('ABC PFARMACY LTD')
                ->setMerchantGMT('+03')
                ->setCountryCode('BG')
                ->setOrder($this->cart->id)
                ->setDescription('order #' . $this->cart->id)
                ->setMerchantUrl(url('/')) // optional
                ->setBackRefUrl(url('back-ref-url')) // optional / required for development
                ->setTerminalID(config('bulbank.terminal_id'))
                ->setMerchantId(config('bulbank.merchant_id'))
                ->setEmailAddress('amsteljlo1983@gmail.com')
                ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
                ->setPrivateKeyPassword(config('bulbank.private_key_pass'));

             $this->boricaFormHtml = $saleRequest->generateForm();
        }
    }
}
