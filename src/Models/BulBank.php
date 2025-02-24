<?php

namespace Lunar\BulBank\Models;

use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;

/**
 * Borica BulBank
 */
abstract class BulBank

{
    const SIGNING_SCHEMA_MAC_ADVANCED = 'MAC_ADVANCED';
    const SIGNING_SCHEMA_MAC_EXTENDED = 'MAC_EXTENDED';
    const SIGNING_SCHEMA_MAC_GENERAL = 'MAC_GENERAL';

    /**
     * Default signing schema
     */
    protected string $signingSchema = self::SIGNING_SCHEMA_MAC_GENERAL;

    protected string $merchantId;

    protected string $publicKey;

    private string $terminalID;

    private string $privateKey;

    private ?string $privateKeyPassword = null;

    private array $environmentUrls = [
        'development' => 'https://3dsgate-dev.borica.bg/cgi-bin/cgi_link',
        'production' => 'https://3dsgate.borica.bg/cgi-bin/cgi_link'
    ];

    private string $environment = 'production';

    public function isProduction(): bool
    {
        return $this->environment == 'production';
    }

    public function isDevelopment(): bool
    {
        return $this->environment == 'development';
    }

    public function getEnvironmentUrl(): string
    {
        if ($this->environment == 'development') {
            return $this->environmentUrls['development'];
        }
        return $this->environmentUrls['production'];
    }

    public function setEnvironment(bool $production = true): BulBank
    {
        if ($production) {
            $this->inProduction();
            return $this;
        }
        $this->inDevelopment();
        return $this;
    }

    public function inProduction(): BulBank
    {
        $this->environment = 'production';
        return $this;
    }

    public function inDevelopment(): BulBank
    {
        $this->environment = 'development';
        return $this;
    }

    public function getTerminalID(): string
    {
        return $this->terminalID;
    }

    /**
     * Set terminal ID
     * @throws ParameterValidationException
     */
    public function setTerminalID(string $terminalID): BulBank
    {
        if (mb_strlen($terminalID) != 8) {
            throw new ParameterValidationException('Terminal ID must be exact 8 characters!');
        }
        $this->terminalID = $terminalID;
        return $this;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @throws ParameterValidationException
     */
    public function setMerchantId(int|string $merchantId): static
    {
        if (mb_strlen($merchantId) < 10 || mb_strlen($merchantId) > 15) {
            throw new ParameterValidationException('Merchant ID must be 10-15 characters');
        }
        $this->merchantId = $merchantId;
        return $this;
    }

    public function setSigningSchemaMacAdvanced(): BulBank
    {
        $this->signingSchema = self::SIGNING_SCHEMA_MAC_ADVANCED;
        return $this;
    }

    public function setSigningSchemaMacExtended(): BulBank
    {
        $this->signingSchema = self::SIGNING_SCHEMA_MAC_EXTENDED;
        return $this;
    }

    public function setSigningSchemaMacGeneral(): BulBank
    {
        $this->signingSchema = self::SIGNING_SCHEMA_MAC_GENERAL;
        return $this;
    }

    /**
     * @throws ParameterValidationException
     */
    public function getPublicKey(): string
    {
        if (empty($this->publicKey)) {
            throw new ParameterValidationException('Please set public key first!');
        }

        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): BulBank
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    public function getSigningSchema(): string
    {
        return $this->signingSchema;
    }

    protected function isSigningSchemaMacExtended(): bool
    {
        return $this->signingSchema == self::SIGNING_SCHEMA_MAC_EXTENDED;
    }

    protected function isSigningSchemaMacAdvanced(): bool
    {
        return $this->signingSchema == self::SIGNING_SCHEMA_MAC_ADVANCED;
    }

    protected function isSigningSchemaMacGeneral(): bool
    {
        return $this->signingSchema == self::SIGNING_SCHEMA_MAC_GENERAL;
    }

    /**
     * Generate signature of data with private key
     *
     * @throws SignatureException
     */
    protected function getPrivateSignature(array $data): string
    {
        $signature = $this->getSignatureSource($data);

        $privateKey = openssl_get_privatekey('file://' . $this->getPrivateKey(), $this->getPrivateKeyPassword());
        if (!$privateKey) {
            throw new SignatureException(openssl_error_string());
        }

        $openSignStatus = openssl_sign($signature, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$openSignStatus) {
            throw new SignatureException(openssl_error_string());
        }

        return strtoupper(bin2hex($signature));
    }

    /**
     * Generate signature source
     */
    protected function getSignatureSource(array $data, bool $isResponse = false): string
    {
        $signature = '';
        foreach ($data as $value) {
            if ($isResponse && mb_strlen($value) == 0) {
                $signature .= '-';
                continue;
            }
            if (!$isResponse && $this->isSigningSchemaMacGeneral() && $value == '-') {
                // да не слага броя символи! Заради тъпото поле RFU (Reserved for Future Use)
                $signature .= $value;
                continue;
            }
            $signature .= mb_strlen($value) . $value;
        }

        if ($isResponse && $this->isSigningSchemaMacGeneral()) {
            // Отново заради тъпото поле RFU (Reserved for Future Use)
            $signature .= '-';
        }

        return $signature;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function setPrivateKey(string $privateKeyPath, string $password = null): BulBank
    {
        $this->privateKey = $privateKeyPath;

        if (!empty($password)) {
            $this->setPrivateKeyPassword($password);
        }

        return $this;
    }

    public function getPrivateKeyPassword(): ?string
    {
        return $this->privateKeyPassword;
    }

    public function setPrivateKeyPassword(?string $privateKeyPassword): BulBank
    {
        $this->privateKeyPassword = $privateKeyPassword;
        return $this;
    }
}
