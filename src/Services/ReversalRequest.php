<?php

namespace Lunar\BulBank\Services;

use Lunar\BulBank\Enums\TransactionType;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Interfaces\RequestInterface;
use Lunar\BulBank\Repositories\Request;
use Lunar\BulBank\Repositories\Response;
use Lunar\BulBank\RequestType\Direct;

class ReversalRequest extends Request implements RequestInterface
{
   /**
     * ReversalRequest constructor.
     */
    public function __construct()
    {
        $this->setTransactionType(TransactionType::REVERSAL());
        $this->requestType = new Direct();
    }

    /**
     * @throws ParameterValidationException
     */
    public function send(): Response|string|null
    {
        $response = parent::send();

        return (new ReversalResponse())
            ->setResponseData(json_decode($response, true))
            ->setPublicKey($this->getPublicKey());
    }

    /**
     * @throws ParameterValidationException|SignatureException
     */
    public function getData(): array
    {
        return array_filter([
            'TERMINAL' => $this->getTerminalID(),
            'TRTYPE' => $this->getTransactionType()->getValue(),
            'AMOUNT' => $this->getAmount(),
            'CURRENCY' => $this->getCurrency(),
            'ORDER' => $this->getOrder(),
            'DESC' => $this->getDescription(),
            'MERCHANT' => $this->getMerchantId(),
            'MERCH_NAME' => $this->getMerchantName(),
            'MERCH_URL' => $this->getMerchantUrl(),
            'EMAIL' => $this->getEmailAddress(),
            'COUNTRY' => $this->getCountryCode(),
            'MERCH_GMT' => $this->getMerchantGMT(),
            'LANG' => $this->getLang(),
            'RRN' => $this->getRrn(),
            'INT_REF' => $this->getIntRef(),
            'TIMESTAMP' => $this->getSignatureTimestamp(),
            'NONCE' => $this->getNonce(),
            'P_SIGN' => $this->generateSignature(),
        ]) + $this->generateAddCustomBoricaOrderId();
    }

    public function generateSignature(): string
    {
        $this->validateRequiredParameters();
        if (!$this->isSigningSchemaMacGeneral()) {
            return $this->getPrivateSignature([
                $this->getTerminalID(),
                $this->getTransactionType()->getValue(),
                $this->getAmount(),
                $this->getCurrency(),
                $this->getOrder(),
                $this->getMerchantId(),
                $this->getSignatureTimestamp(),
                $this->getNonce()
            ]);
        }

        return parent::generateSignature();
    }

    public function validateRequiredParameters(): void
    {
        if (empty($this->getPublicKey())) {
            throw new ParameterValidationException('Please set public key for validation response!');
        }

        if (empty($this->getIntRef())) {
            throw new ParameterValidationException('Internal reference is empty!');
        }

        if (empty($this->getRrn())) {
            throw new ParameterValidationException('Payment reference is empty!');
        }

        parent::validateRequiredParameters();
    }
}
