<div>
        @php
    $saleRequest = (new Lunar\BulBank\Services\SaleRequest())
        ->inDevelopment() // set to development
        ->setAmount(123.32)
        ->setCountryCode('BG')
        ->setMerchantName('aaaa')
        ->setAdCustBorOrderId('22')
        ->setEmailAddress('dddd@ff.bg')
        ->setOrder(123456)
        ->setDescription('test')
        ->setMerchantUrl(url('/')) // optional
        ->setBackRefUrl(url('back-ref-url')) // optional / required for development
        ->setTerminalID('V5402041')
        ->setMerchantId('6210035458')
        ->setPrivateKey(base_path('borica.key'), 'pass')
        ->setSigningSchemaMacGeneral()
        ->setPrivateKeyPassword('pass');

    $formHtml = $saleRequest->generateForm(); // only generate hidden html form with filled inputs

    @endphp
    {!!  $formHtml !!}
</div>
