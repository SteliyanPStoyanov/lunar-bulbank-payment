<?php

namespace Lunar\BulBank\Repositories;


use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Models\BulBank;
use Lunar\BulBank\Models\TransactionType;

/**
 * Borica request
 */
abstract class Request extends BulBank
{
   /**
     * @var mixed
     */
    private mixed $signatureTimestamp;


    /**
     * @var string
     */
    private string $backRefUrl;

    /**
     * @var mixed
     */
    private mixed $amount = null;

    /**
     * @var string
     */
    private string $currency = 'BGN';

    /**
     * @var string
     */
    private string $description;

    /**
     * @var TransactionType
     */
    private TransactionType $transactionType;

    /**
     * @var mixed
     */
    private mixed $order;

    /**
     * @var string
     */
    private string $nonce;

    /**
     * Get description
     *
     * @return mixed
     */
    public function getDescription(): mixed
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description Описание на поръчката.
     *
     * @return Request
     * @throws ParameterValidationException
     */
    public function setDescription(string $description): static
    {
        if (mb_strlen($description) > 50) {
            throw new ParameterValidationException('Description must be max 50 digits');
        }
        $this->description = $description;
        return $this;
    }

    /**
     * Get back ref url
     *
     * @return string
     */
    public function getBackRefUrl(): string
    {
        return $this->backRefUrl;
    }

    /**
     * Set back ref url
     *
     * @param string $backRefUrl URL на търговеца за изпращане на резултата от авторизацията.
     *
     * @return Request
     * @throws ParameterValidationException
     */
    public function setBackRefUrl(string $backRefUrl): static
    {
        if (!filter_var($backRefUrl, FILTER_VALIDATE_URL)) {
            throw new ParameterValidationException('Backref url is not valid!');
        }

        $this->backRefUrl = $backRefUrl;
        return $this;
    }

    /**
     * Get order
     *
     * @return mixed
     */
    public function getOrder(): mixed
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param integer|string $order Номер на поръчката за търговеца, 6 цифри, който трябва да бъде уникален за деня.
     *
     * @return Request
     * @throws ParameterValidationException
     */
    public function setOrder(int|string $order): static
    {
        if (mb_strlen($order) > 6) {
            throw new ParameterValidationException('Order must be max 6 digits');
        }

        $this->order = str_pad($order, 6, "0", STR_PAD_LEFT);
        return $this;
    }

    /**
     * Get transaction type
     *
     * @return TransactionType
     */
    public function getTransactionType(): TransactionType
    {
        return $this->transactionType;
    }

    /**
     * Set transaction type
     *
     * @param TransactionType $transactionType Тип на транзакцията.
     *
     * @return Request
     */
    public function setTransactionType(TransactionType $transactionType): static
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * Get amount
     *
     * @return float|int|string|null
     */
    public function getAmount(): float|int|string|null
    {
        return $this->amount;
    }

    /**
     * Set amount
     *
     * @param float|integer|string $amount Обща стойност на поръчката по стандарт ISO_4217 с десетичен разделител точка.
     *
     * @return Request
     */
    public function setAmount(float|int|string $amount): static
    {
        $this->amount = number_format($amount, 2, '.', '');
        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Set currency
     *
     * @param string $currency Валута на поръчката: три буквен код на валута по стандарт ISO 4217.
     *
     * @return Request
     * @throws ParameterValidationException
     */
    public function setCurrency(string $currency): static
    {
        if (mb_strlen($currency) != 3) {
            throw new ParameterValidationException('3 character currency code');
        }
        $this->currency = mb_strtoupper($currency);
        return $this;
    }

    /**
     * Get signature timestamp
     *
     * @return string
     */
    public function getSignatureTimestamp(): string
    {
        if (empty($this->signatureTimestamp)) {
            $this->setSignatureTimestamp();
        }

        return $this->signatureTimestamp;
    }

    /**
     * Set signature timestamp
     *
     * @param string|null $signatureTimestamp Дата на подпис/изпращане на данните.
     *
     * @return Request
     */
    public function setSignatureTimestamp(string $signatureTimestamp = null): static
    {
        if (empty($signatureTimestamp)) {
            $this->signatureTimestamp = gmdate('YmdHis');
            return $this;
        }

        $this->signatureTimestamp = $signatureTimestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        if (!empty($this->nonce)) {
            return $this->nonce;
        }
        $this->setNonce(strtoupper(bin2hex(openssl_random_pseudo_bytes(16))));
        return $this->nonce;
    }

    /**
     * @param string $nonce Nonce.
     *
     * @return Request
     */
    public function setNonce(string $nonce): static
    {
        $this->nonce = $nonce;
        return $this;
    }

}
