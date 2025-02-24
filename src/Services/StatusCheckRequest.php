<?php
namespace Lunar\BulBank\Services;


use Lunar\BulBank\Enums\TransactionType;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Interfaces\RequestInterface;
use Lunar\BulBank\Repositories\Request;
use Lunar\BulBank\Repositories\Response;
use Lunar\BulBank\RequestType\Direct;

class StatusCheckRequest extends Request implements RequestInterface
{
    private TransactionType $originalTransactionType;

    public function __construct()
    {
        $this->setTransactionType(TransactionType::TRANSACTION_STATUS_CHECK());
        $this->setRequestType(new Direct());
    }

    /**
     * @throws ParameterValidationException
     */
    public function send(): Response|string|null
    {
        $response = parent::send();

        return (new StatusCheckResponse())
            ->setResponseData(json_decode($response, true))
            ->setPublicKey($this->getPublicKey());
    }

    /**
     * @throws ParameterValidationException|SignatureException
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

    public function getOriginalTransactionType(): TransactionType
    {
        return $this->originalTransactionType;
    }

    public function setOriginalTransactionType(TransactionType $tranType): static
    {
        $this->originalTransactionType = $tranType;
        return $this;
    }

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
}
