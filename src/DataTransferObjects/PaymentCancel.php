<?php

namespace Lunar\BulBank\DataTransferObjects;

class PaymentCancel
{
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
        public ?int $orderId = null
    ) {
        //
    }
}
