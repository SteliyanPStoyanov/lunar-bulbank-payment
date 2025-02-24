<?php

namespace Lunar\BulBank\Repositories;

use Lunar\BulBank\Enums\Language;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Models\BulBank;
use Lunar\BulBank\Enums\TransactionType;
use Lunar\BulBank\RequestType\RequestType;

/**
 * Borica request
 */
abstract class Request extends BulBank
{

    protected mixed $signatureTimestamp;

    protected string|int|null|float $amount = null;

    protected string $currency = 'BGN';

    protected string $description;

    protected TransactionType $transactionType;

    protected mixed $order;

    protected string $nonce;

    protected array $mInfo;

    protected ?string $lang = null;

    protected string $merchantUrl;

    protected string $merchantName;

    protected string $emailAddress;

    protected string $countryCode;

    protected string $merchantGMT;

    protected string $addCustomBoricaOrderId;

    protected string $rrn;

    protected string $intRef;

    protected RequestType $requestType;

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @throws ParameterValidationException
     */
    public function setDescription(string $description): Request
    {
        if (mb_strlen($description) > 50) {
            throw new ParameterValidationException('Description must be max 50 digits');
        }
        $this->description = $description;
        return $this;
    }

    public function getOrder(): mixed
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param integer|string $order Номер на поръчката за търговеца, 6 цифри, който трябва да бъде уникален за деня.
     *
     * @throws ParameterValidationException
     */
    public function setOrder(int|string $order):Request
    {
        if (mb_strlen($order) > 6) {
            throw new ParameterValidationException('Order must be max 6 digits');
        }

        $this->order = str_pad($order, 6, "0", STR_PAD_LEFT);
        return $this;
    }

    public function getTransactionType(): TransactionType
    {
        return $this->transactionType;
    }

    public function setTransactionType(TransactionType $transactionType): Request
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    public function getAmount(): float|int|string|null
    {
        return $this->amount;
    }

    public function setAmount(float|int|string $amount): Request
    {
        $this->amount = number_format($amount, 2, '.', '');
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @throws ParameterValidationException
     */
    public function setCurrency(string $currency): Request
    {
        if (mb_strlen($currency) != 3) {
            throw new ParameterValidationException('3 character currency code');
        }
        $this->currency = mb_strtoupper($currency);
        return $this;
    }

    public function getSignatureTimestamp(): string
    {
        if (empty($this->signatureTimestamp)) {
            $this->setSignatureTimestamp();
        }

        return $this->signatureTimestamp;
    }

    public function setSignatureTimestamp(string $signatureTimestamp = null): Request
    {
        if (empty($signatureTimestamp)) {
            $this->signatureTimestamp = gmdate('YmdHis');
            return $this;
        }

        $this->signatureTimestamp = $signatureTimestamp;
        return $this;
    }

    public function getNonce(): string
    {
        if (!empty($this->nonce)) {
            return $this->nonce;
        }
        $this->setNonce(strtoupper(bin2hex(openssl_random_pseudo_bytes(16))));
        return $this->nonce;
    }

    public function setNonce(string $nonce): Request
    {
        $this->nonce = $nonce;
        return $this;
    }

    public function getMInfo(): string
    {
        if (!empty($this->mInfo)) {
            return base64_encode(json_encode($this->mInfo));
        }
        return '';
    }

    /**
     * @throws ParameterValidationException
     */
    public function setMInfo(array $mInfo): Request
    {
        if (!isset($mInfo['cardholderName']) ||
            (!isset($mInfo['email']) && !isset($mInfo['mobilePhone']))) {
            throw new ParameterValidationException('CardholderName and email or MobilePhone must be provided');
        }

        if (strlen($mInfo['cardholderName']) > 45) {
            throw new ParameterValidationException('CardHolderName must be at most 45 characters');
        }

        if (isset($mInfo['email']) && !filter_var($mInfo['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ParameterValidationException('Email must be a valid email address');
        }

        if (isset($mInfo['mobilePhone'])) {
            if (!isset($mInfo['mobilePhone']['cc']) || !isset($mInfo['mobilePhone']['subscriber'])) {
                throw new ParameterValidationException('MobilePhone must contain both cc and subscriber');
            }
        }

        $this->mInfo = $mInfo;
        return $this;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @throws ParameterValidationException
     */
    public function setLang(?string $lang): static
    {
        if (empty($lang)) {
            $this->lang = null;
        } else {
            if (mb_strlen($lang) != 2) {
                throw new ParameterValidationException('2 character language code');
            }
            $lang = mb_strtoupper($lang);
            if (!Language::isValid($lang)) {
                throw new ParameterValidationException('Not a valid language code');
            }
            $this->lang = $lang;
        }
        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @throws ParameterValidationException
     */
    public function setCountryCode(string $countryCode): static
    {
        if (mb_strlen($countryCode) != 2) {
            throw new ParameterValidationException('Country code must be exact 2 characters (ISO2)');
        }
        $this->countryCode = strtoupper($countryCode);
        return $this;
    }

    public function getMerchantGMT(): ?string
    {
        if (empty($this->merchantGMT)) {
            $this->setMerchantGMT(date('O'));
        }
        return $this->merchantGMT;
    }

    public function setMerchantGMT(string $merchantGMT): Request
    {
        $this->merchantGMT = $merchantGMT;
        return $this;
    }

    public function getMerchantName(): string
    {
        return $this->merchantName;
    }

    public function setMerchantName(string $merchantName): Request
    {
        $this->merchantName = $merchantName;
        return $this;
    }

    public function getMerchantUrl(): string
    {
        return $this->merchantUrl;
    }

    /**
     * @throws ParameterValidationException
     */
    public function setMerchantUrl(string $merchantUrl): Request
    {
        if (mb_strlen($merchantUrl) > 250) {
            throw new ParameterValidationException('Merchant URL must be maximum 250 characters');
        }

        $this->merchantUrl = $merchantUrl;
        return $this;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @throws ParameterValidationException
     */
    public function setEmailAddress(string $emailAddress): Request
    {
        if (mb_strlen($emailAddress) > 80) {
            throw new ParameterValidationException('Email address for notifications must be maximum 80 characters');
        }
        $this->emailAddress = $emailAddress;
        return $this;
    }

    protected function generateAddCustomBoricaOrderId(): array
    {
        $orderString = $this->getAddCustomBoricaOrderId();

        if (empty($orderString)) {
            $orderString = $this->getOrder();
        }

        /*
         * полето не трябва да съдържа символ “;”
         */
        $orderString = str_ireplace(';', '', $orderString);

        return [
            'ADD.CUSTOM_BORICA_ORDER_ID' => mb_substr($orderString, 0, 22),
            'ADDENDUM' => 'AD,TD',
        ];
    }

    public function getAddCustomBoricaOrderId(): string
    {
        return $this->addCustomBoricaOrderId;
    }

    public function setAddCustomBoricaOrderId(string $addCustomBoricaOrderId): Request
    {
        $this->addCustomBoricaOrderId = $addCustomBoricaOrderId;
        return $this;
    }

    public function setRrn(string $rrn): Request
    {
        $this->rrn = $rrn;
        return $this;
    }

    public function setIntRef(string $intRef): Request
    {
        $this->intRef = $intRef;
        return $this;
    }

    public function getRrn(): string
    {
        return $this->rrn;
    }

    public function getIntRef(): string
    {
        return $this->intRef;
    }

    /**
     * @throws SignatureException
     * @throws ParameterValidationException
     */
    public function generateSignature(): string
    {
        $this->validateRequiredParameters();

        if ($this->isSigningSchemaMacExtended()) {
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

        if ($this->isSigningSchemaMacAdvanced()) {
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

        // Default MAC_GENERAL
        return $this->getPrivateSignature([
            $this->getTerminalID(),
            $this->getTransactionType()->getValue(),
            $this->getAmount(),
            $this->getCurrency(),
            $this->getOrder(),
            $this->getSignatureTimestamp(),
            $this->getNonce(),
            '-'
        ]);
    }


    /**
     * @throws ParameterValidationException
     */
    public function validateRequiredParameters(): void
    {
        if (empty($this->getTransactionType())) {
            throw new ParameterValidationException('Transaction type is empty!');
        }

        if (empty($this->getAmount())) {
            throw new ParameterValidationException('Amount is empty!');
        }

        if (empty($this->getCurrency())) {
            throw new ParameterValidationException('Currency is empty!');
        }

        if (empty($this->getOrder())) {
            throw new ParameterValidationException('Order is empty!');
        }

        if (empty($this->getDescription())) {
            throw new ParameterValidationException('Description is empty!');
        }

        if (empty($this->getMerchantId())) {
            throw new ParameterValidationException('Merchant ID is empty!');
        }

        if (empty($this->getTerminalID())) {
            throw new ParameterValidationException('TerminalID is empty!');
        }
    }

    public function getRequestType(): RequestType
    {
        return $this->requestType;
    }

    public function setRequestType(RequestType $requestType): Request
    {
        $this->requestType = $requestType;
        return $this;
    }

    public function generateForm()
    {
        return $this->getRequestType()
            ->setUrl($this->getEnvironmentUrl())
            ->setData($this->getData())
            ->generateForm();
    }

    public function send()
    {
        return $this->requestType
            ->setUrl($this->getEnvironmentUrl())
            ->setData($this->getData())
            ->send();
    }
}
