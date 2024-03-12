<?php

namespace Lunar\BulBank\Services;

use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Interfaces\RequestInterface;
use Lunar\BulBank\Models\TransactionType;
use Lunar\BulBank\Repositories\Request;

class SaleRequest extends Request implements RequestInterface
{

    /**
     * @var string
     */
    protected  $merchantUrl;

    /**
     * @var string
     */
    protected  $merchantName;

    /**
     * @var string
     */
    protected  $emailAddress;

    /**
     * @var string
     */
    protected  $countryCode;

    /**
     * @var string
     */
    protected  $merchantGMT;

    /**
     * @var string
     */
    protected  $adCustBorOrderId;

    /**
     * Sale constructor.
     */
    public function __construct()
    {
        $this->setTransactionType(TransactionType::SALE());
    }

    /**
     * Send to borica. Generate form and auto submit with JS.
     *
     * @return void
     * @throws SignatureException|ParameterValidationException
     */
    public function send()
    {
        $html = $this->generateForm();

        $html .= '<script>
            document.getElementById("borica3dsRedirectForm").submit()
        </script>';

        die($html);
    }

    /**
     * Generate HTML hidden form
     *
     * @return string
     * @throws SignatureException|ParameterValidationException
     */
    public function generateForm(): string
    {
        $html = '<form
	        action="' . $this->getEnvironmentUrl() . '"
	        method="POST"
	        id="borica3dsRedirectForm"
        >';

        $inputs = $this->getData();
        foreach ($inputs as $key => $value) {
            $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
        }

        $html .= '<button class="px-5 py-3 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500" type="submit" wire:key="payment_submit_btn">
                        <span wire:loading.remove.delay="" wire:target="checkout">
                            Подайте Поръчка
                        </span>
                        <span wire:loading.delay="" wire:target="checkout">
                            <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>';

        $html .= '</form>';

        return $html;
    }

    /**
     * Get data required for request to borica
     *
     * @return array
     * @throws SignatureException|ParameterValidationException
     */
    public function getData(): array
    {
        return [
                'NONCE' => $this->getNonce(),
                'P_SIGN' => $this->generateSignature(),

                'TRTYPE' => $this->getTransactionType()->getValue(),
                'COUNTRY' => $this->getCountryCode(),
                'CURRENCY' => $this->getCurrency(),

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
                'BACKREF' => $this->getBackRefUrl(),
            ] + $this->generateAdCustBorOrderId();
    }

    /**
     * Generate signature of data
     *
     * @return string
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

        // Default MAC_ADVANCED
        return $this->getPrivateSignature([
            $this->getTerminalID(),
            $this->getTransactionType()->getValue(),
            $this->getAmount(),
            $this->getCurrency(),
            $this->getOrder(),
            $this->getSignatureTimestamp(),
            $this->getNonce()
        ] , true);
    }

    /**
     * Validate required fields to post
     *
     * @return void
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

        if (empty($this->getBackRefUrl()) && $this->isDevelopment()) {
            throw new ParameterValidationException('Back ref url is empty! (required in development)');
        }

        if (empty($this->getMerchantId())) {
            throw new ParameterValidationException('Merchant ID is empty!');
        }

        if (empty($this->getTerminalID())) {
            throw new ParameterValidationException('TerminalID is empty!');
        }
    }

    /**
     * Get country code
     *
     * @return string
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * Set country code
     *
     * @param string $countryCode Двубуквен код на държавата, където се намира магазинът на търговеца.
     *
     * @return SaleRequest
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

    /**
     * Get merchant GMT
     *
     * @return string|null
     */
    public function getMerchantGMT(): ?string
    {
        if (empty($this->merchantGMT)) {
            $this->setMerchantGMT(date('O'));
        }
        return $this->merchantGMT;
    }

    /**
     * Set merchant GMT
     *
     * @param string $merchantGMT Отстояние на часовата зона на търговеца от UTC/GMT  (напр. +03).
     *
     * @return SaleRequest
     */
    public function setMerchantGMT(string $merchantGMT): static
    {
        $this->merchantGMT = $merchantGMT;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantName(): ?string
    {
        return $this->merchantName;
    }

    /**
     * @param string $merchantName Merchant name.
     *
     * @return SaleRequest
     */
    public function setMerchantName(string $merchantName): static
    {
        $this->merchantName = $merchantName;
        return $this;
    }

    /**
     * Get merchant URL
     *
     * @return string
     */
    public function getMerchantUrl(): ?string
    {
        return $this->merchantUrl;
    }

    /**
     * Set merchant URL
     *
     * @param string $merchantUrl URL на web сайта на търговеца.
     *
     * @return SaleRequest
     * @throws ParameterValidationException
     */
    public function setMerchantUrl(string $merchantUrl): static
    {
        if (mb_strlen($merchantUrl) > 250) {
            throw new ParameterValidationException('Merchant URL must be maximum 250 characters');
        }

        $this->merchantUrl = $merchantUrl;
        return $this;
    }

    /**
     * Get notification email address
     *
     * @return string
     */
    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * Set notification email address
     *
     * @param string $emailAddress E-mail адрес за уведомления.
     *
     * @return SaleRequest
     * @throws ParameterValidationException
     */
    public function setEmailAddress(string $emailAddress): static
    {
        if (mb_strlen($emailAddress) > 80) {
            throw new ParameterValidationException('Email address for notifications must be maximum 80 characters');
        }
        $this->emailAddress = $emailAddress;
        return $this;
    }

    /**
     * Generate AD.CUST_BOR_ORDER_ID borica field
     *
     * @return array
     */
    private function generateAdCustBorOrderId(): array
    {
        $orderString = $this->getAdCustBorOrderId();

        if (empty($orderString)) {
            $orderString = $this->getOrder();
        }

        /*
         * полето не трябва да съдържа символ “;”
         */
        $orderString = str_ireplace(';', '', $orderString);

        return [
            'AD.CUST_BOR_ORDER_ID' => mb_substr($orderString, 0, 22),
            'ADDENDUM' => 'AD,TD',
        ];
    }

    /**
     * Get 'AD.CUST_BOR_ORDER_ID' field
     *
     * @return string
     */
    public function getAdCustBorOrderId(): ?string
    {
        return $this->adCustBorOrderId;
    }

    /**
     * Set 'AD.CUST_BOR_ORDER_ID' field
     *
     * @param string $adCustBorOrderId Идентификатор на поръчката за Банката на търговеца във финансовите файлове.
     *
     * @return SaleRequest
     */
    public function setAdCustBorOrderId(string $adCustBorOrderId): static
    {
        $this->adCustBorOrderId = $adCustBorOrderId;
        return $this;
    }
}
