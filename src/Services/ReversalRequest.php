<?php

namespace Lunar\BulBank\Services;

use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SendingException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Interfaces\RequestInterface;
use Lunar\BulBank\Models\TransactionType;
use Lunar\BulBank\Repositories\Request;

class ReversalRequest extends Request implements RequestInterface
{
    /**
     * @var string
     */
    protected string $rrn;

    /**
     * @var string
     */
    protected string $intRef;

    /**
     * @var string
     */
    protected string $merchantName;

    /**
     * StatusCheckRequest constructor.
     */
    public function __construct()
    {
        $this->setTransactionType(TransactionType::REVERSAL());
    }

    /**
     * Send data to borica
     *
     * @return ReversalResponse
     * @throws SignatureException|ParameterValidationException|SendingException
     */
    public function send(): ReversalResponse
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->getEnvironmentUrl());
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->getData()));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        if (curl_error($ch)) {
            throw new SendingException(curl_error($ch));
        }
        curl_close($ch);

        return (new ReversalResponse())
            ->setResponseData(json_decode($response, true))
            ->setPublicKey($this->getPublicKey());
    }

    /**
     * @return array
     * @throws ParameterValidationException|SignatureException
     */
    public function getData(): array
    {
        return [
            'TERMINAL' => $this->getTerminalID(),
            'TRTYPE' => $this->getTransactionType()->getValue(),
            'AMOUNT' => $this->getAmount(),
            'CURRENCY' => $this->getCurrency(),
            'ORDER' => $this->getOrder(),
            'DESC' => $this->getDescription(),
            'MERCHANT' => $this->getMerchantId(),
            'MERCH_NAME' => $this->getMerchantName(),
            'RRN' => $this->getRrn(),
            'INT_REF' => $this->getIntRef(),
            'TIMESTAMP' => $this->getSignatureTimestamp(),
            'NONCE' => $this->getNonce(),
            'P_SIGN' => $this->generateSignature(),
        ];
    }

    /**
     * @return string
     */
    public function getRrn(): string
    {
        return $this->rrn;
    }

    /**
     * @return string
     */
    public function getIntRef(): string
    {
        return $this->intRef;
    }

    /**
     * @return string
     *
     * @throws ParameterValidationException|SignatureException
     */
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

        return $this->getPrivateSignature([
            $this->getTerminalID(),
            $this->getTransactionType()->getValue(),
            $this->getAmount(),
            $this->getCurrency(),
            $this->getOrder(),
            $this->getSignatureTimestamp(),
            $this->getNonce()
        ]);
    }

    /**
     * @return void
     * @throws ParameterValidationException
     */
    public function validateRequiredParameters(): void
    {
        if (empty($this->getTransactionType())) {
            throw new ParameterValidationException('Transaction type is empty!');
        }

        if (empty($this->getOrder())) {
            throw new ParameterValidationException('Order is empty!');
        }

        if (empty($this->getPublicKey())) {
            throw new ParameterValidationException('Please set public key for validation response!');
        }

        if (empty($this->getTerminalID())) {
            throw new ParameterValidationException('TerminalID is empty!');
        }

        if (empty($this->getIntRef())) {
            throw new ParameterValidationException('Internal reference is empty!');
        }

        if (empty($this->getRrn())) {
            throw new ParameterValidationException('Payment reference is empty!');
        }
    }

    /**
     * @return array
     * @throws ParameterValidationException|SignatureException
     */
    public function generateForm()
    {
        return $this->getData();
    }

    /**
     * Set transaction reference.
     *
     * @param string $rrn Референция на транзакцията.
     *
     * @return ReversalRequest
     */
    public function setRrn(string $rrn): static
    {
        $this->rrn = $rrn;
        return $this;
    }

    /**
     * Set transaction internal reference.
     *
     * @param string $intRef Вътрешна референция на транзакцията.
     *
     * @return ReversalRequest
     */
    public function setIntRef(string $intRef): static
    {
        $this->intRef = $intRef;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantName(): string
    {
        return $this->merchantName;
    }

    /**
     * @param string $merchantName Merchant name.
     *
     * @return ReversalRequest
     */
    public function setMerchantName(string $merchantName): static
    {
        $this->merchantName = $merchantName;
        return $this;
    }
}
