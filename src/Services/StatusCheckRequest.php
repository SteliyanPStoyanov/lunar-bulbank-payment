<?php
namespace Lunar\BulBank\Services;

use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SendingException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Interfaces\RequestInterface;
use Lunar\BulBank\Models\TransactionType;
use Lunar\BulBank\Repositories\Request;

class StatusCheckRequest extends Request implements RequestInterface
{

    /**
     * Original transaction type / TRAN_TRTYPE
     *
     * @var TransactionType
     */
    private TransactionType $originalTransactionType;

    /**
     * @var array
     */
    private array $sendResponse;

    /**
     * StatusCheckRequest constructor.
     */
    public function __construct()
    {
        $this->setTransactionType(TransactionType::TRANSACTION_STATUS_CHECK());
    }

    /**
     * Send data to borica
     *
     * @return StatusCheckResponse
     * @throws SignatureException|ParameterValidationException|SendingException
     */
    public function send(): StatusCheckResponse
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

        return (new StatusCheckResponse())
            ->setResponseData(json_decode($response, true))
            ->setPublicKey($this->getPublicKey());
    }

    /**
     * @return array
     * @throws SignatureException
     * @throws ParameterValidationException
     */
    public function getData(): array
    {
        return [
            'TERMINAL' => $this->getTerminalID(),
            'TRTYPE' => $this->getTransactionType()->getValue(),
            'ORDER' => $this->getOrder(),
            'TRAN_TRTYPE' => $this->getOriginalTransactionType()->getValue(),

            'NONCE' => $this->getNonce(),
            'P_SIGN' => $this->generateSignature(),
        ];
    }

    /**
     * @return TransactionType
     */
    public function getOriginalTransactionType(): TransactionType
    {
        return $this->originalTransactionType;
    }

    /**
     * Set original transaction type
     *
     * @param TransactionType $tranType Original transaction type.
     *
     * @return StatusCheckRequest
     */
    public function setOriginalTransactionType(TransactionType $tranType): static
    {
        $this->originalTransactionType = $tranType;
        return $this;
    }

    /**
     * @return string
     * @throws SignatureException
     * @throws ParameterValidationException
     */
    public function generateSignature(): string
    {
        $this->validateRequiredParameters();
        return $this->getPrivateSignature([
            $this->getTerminalID(),
            $this->getTransactionType()->getValue(),
            $this->getOrder(),
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

        if (empty($this->getOriginalTransactionType())) {
            throw new ParameterValidationException('Original transaction type is empty!');
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
    }

    /**
     * @return array
     * @throws SignatureException
     * @throws ParameterValidationException
     */
    public function generateForm(): array
    {
        return $this->getData();
    }
}
