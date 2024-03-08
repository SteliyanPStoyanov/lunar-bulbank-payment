<div>
    @php
        $saleRequest = (new Lunar\BulBank\Services\SaleRequest())
               ->setSigningSchemaMacGeneral()
               ->inDevelopment() // set to development
               ->setAmount($cart->total->value / 100)
               ->setMerchantName('ABC PFARMACY LTD')
               ->setMerchantGMT('+03')
               ->setCountryCode('BG')
               ->setOrder($cart->id)
               ->setDescription('order #'.$cart->id)
               ->setMerchantUrl(url('/')) // optional
               ->setBackRefUrl(url('back-ref-url')) // optional / required for development
               ->setTerminalID(config('bulbank.terminal_id'))
               ->setMerchantId(config('bulbank.merchant_id'))
               ->setEmailAddress('amsteljlo1983@gmail.com')
               ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
               ->setPrivateKeyPassword(config('bulbank.private_key_pass'));
           $formHtml = $saleRequest->generateForm(); // only generate hidden html form with filled inputs
        @endphp
    {!! $formHtml !!}
</div>
