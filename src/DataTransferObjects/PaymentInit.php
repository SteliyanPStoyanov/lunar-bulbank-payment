<?php

namespace Lunar\BulBank\DataTransferObjects;

class PaymentInit
{
    public function __construct(
        public bool $success = false,
        public ?string $form = null,
        public ?int $orderId = null
    ) {
        //
    }
}