<div>
        @php
            $saleRequest = (new Lunar\BulBank\Services\SaleRequest())
                   ->inDevelopment() // set to development
                   ->setSigningSchemaMacGeneral()
                   ->setAmount(1.32)
                   ->setOrder(123456)
                   ->setDescription('test')
                   ->setMerchantUrl(url('/')) // optional
                   ->setBackRefUrl(url('back-ref-url')) // optional / required for development
                   ->setTerminalID(config('bulbank.terminal_id'))
                   ->setMerchantId(config('bulbank.merchant_id'))
                   ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
                   ->setPrivateKeyPassword(config('bulbank.private_key_pass'));
               $formHtml = $saleRequest->generateForm(); // only generate hidden html form with filled inputs
        @endphp
    {!! $formHtml !!}
</div>
