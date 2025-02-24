<?php

namespace Lunar\BulBank\Services;

use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Interfaces\RequestInterface;
use Lunar\BulBank\Enums\TransactionType;
use Lunar\BulBank\Repositories\Request;
use Lunar\BulBank\Repositories\Response;
use Lunar\BulBank\RequestType\HtmlForm;

class SaleRequest extends Request implements RequestInterface
{

    public function __construct()
    {
        $this->setTransactionType(TransactionType::SALE());
        $this->setRequestType(new HtmlForm());
    }

    public function send(): Response|string|null
    {
        $html = parent::send();
        die($html);
    }


    /**
     * @throws SignatureException
     * @throws ParameterValidationException
     */
    public function getData(): array
    {
        return array_filter([
                'NONCE' => $this->getNonce(),
                'P_SIGN' => $this->generateSignature(),

                'TRTYPE' => $this->getTransactionType()->getValue(),
                'COUNTRY' => $this->getCountryCode(),
                'CURRENCY' => $this->getCurrency(),
                'LANG' => $this->getLang(),

                'MERCH_GMT' => $this->getMerchantGMT(),
                'MERCHANT' => $this->getMerchantId(),
                'MERCH_NAME' => $this->getMerchantName(),
                'MERCH_URL' => $this->getMerchantUrl(),
                'EMAIL' => $this->getEmailAddress(),

                'ORDER' => $this->getOrder(),
                'AMOUNT' => $this->getAmount(),
                'DESC' => $this->getDescription(),
                'TIMESTAMP' => $this->getSignatureTimestamp(),

                'TERMINAL' => $this->getTerminalID(),

                'M_INFO' => $this->getMInfo(),

            ]) + $this->generateAddCustomBoricaOrderId();
    }
}
