<div>
        @php
    $saleRequest = (new Lunar\BulBank\Services\SaleRequest())
        ->inDevelopment() // set to development
        ->setAmount(123.32)
        ->setCountryCode('BG')
        ->setMerchantName(config('bulbank.merchant_name'))
        ->setAdCustBorOrderId('22')
        ->setEmailAddress('dddd@ff.bg')
        ->setOrder(123456)
        ->setDescription('test')
        ->setMerchantUrl(url('/')) // optional
        ->setBackRefUrl(url('back-ref-url')) // optional / required for development
        ->setTerminalID(config('bulbank.terminal_id'))
        ->setMerchantId(config('bulbank.merchant_id'))
        ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
        ->setSigningSchemaMacGeneral()
        ->setPrivateKeyPassword(config('bulbank.private_key_pass'));

    $formHtml = $saleRequest->generateForm(); // only generate hidden html form with filled inputs

    @endphp
    {!!  $formHtml !!}
</div>
