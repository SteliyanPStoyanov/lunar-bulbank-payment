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
     *
     * @var string
     */
    protected string $signingSchema = self::SIGNING_SCHEMA_MAC_GENERAL;

    /**
     * @var string
     */
    protected string $merchantId;

    /**
     * @var string
     */
    protected string $publicKey;

    /**
     * @var string
     */
    private string $terminalID;

    /**
     * @var string
     */
    private string $privateKey;

    /**
     * @var string|null
     */
    private ?string $privateKeyPassword = null;

    /**
     * @var string[]
     */
    private array $environmentUrls = [
        'development' => 'https://3dsgate-dev.borica.bg/cgi-bin/cgi_link',
        'production' => 'https://3dsgate.borica.bg/cgi-bin/cgi_link'
    ];

    /**
     * In develop mode of application
     *
     * @var string
     */
    private string $environment = 'production';

    /**
     * @return boolean
     */
    public function isProduction(): bool
    {
        return $this->environment == 'production';
    }

    /**
     * @return boolean
     */
    public function isDevelopment(): bool
    {
        return $this->environment == 'development';
    }

    /**
     * @return string
     */
    public function getEnvironmentUrl(): string
    {
        if ($this->environment == 'development') {
            return $this->environmentUrls['development'];
        }
        return $this->environmentUrls['production'];
    }

    /**
     * Switch environment to development/production
     *
     * @param boolean $production True - production / false - development.
     *
     * @return BulBank
     */
    public function setEnvironment(bool $production = true): static
    {
        if ($production) {
            $this->inProduction();
            return $this;
        }
        $this->inDevelopment();
        return $this;
    }

    /**
     * Switch to production mode
     *
     * @return BulBank
     */
    public function inProduction(): static
    {
        $this->environment = 'production';
        return $this;
    }

    /**
     * Switch to development mode
     *
     * @return BulBank
     */
    public function inDevelopment(): static
    {
        $this->environment = 'development';
        return $this;
    }

    /**
     * Get terminal ID
     *
     * @return string
     */
    public function getTerminalID(): string
    {
        return $this->terminalID;
    }

    /**
     * Set terminal ID
     *
     * @param string $terminalID Terminal ID.
     *
     * @return BulBank
     * @throws ParameterValidationException
     */
    public function setTerminalID(string $terminalID): static
    {
        if (mb_strlen($terminalID) != 8) {
            throw new ParameterValidationException('Terminal ID must be exact 8 characters!');
        }
        $this->terminalID = $terminalID;
        return $this;
    }

    /**
     * Get merchant ID
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * Set merchant ID
     *
     * @param integer|string $merchantId Merchant ID.
     *
     * @return BulBank
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

    /**
     * Switch signing schema to MAC_ADVANCED
     *
     * @return BulBank
     */
    public function setSigningSchemaMacAdvanced(): static
    {
        $this->signingSchema = self::SIGNING_SCHEMA_MAC_ADVANCED;
        return $this;
    }

    /**
     * Switch signing schema to MAC_EXTENDED
     *
     * @return BulBank
     */
    public function setSigningSchemaMacExtended(): static
    {
        $this->signingSchema = self::SIGNING_SCHEMA_MAC_EXTENDED;
        return $this;
    }

    /**
     * Switch signing schema to MAC_GENERAL
     *
     * @return BulBank
     */
    public function setSigningSchemaMacGeneral(): static
    {
        $this->signingSchema = self::SIGNING_SCHEMA_MAC_GENERAL;
        return $this;
    }

    /**
     * Get public key
     *
     * @return string
     * @throws ParameterValidationException
     */
    public function getPublicKey(): string
    {
        if (empty($this->publicKey)) {
            throw new ParameterValidationException('Please set public key first!');
        }

        return $this->publicKey;
    }

    /**
     * Set public key
     *
     * @param string $publicKey Public key path.
     *
     * @return BulBank
     */
    public function setPublicKey(string $publicKey): static
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSigningSchema(): string
    {
        return $this->signingSchema;
    }

    /**
     * Is MAC_EXTENDED signing schema?
     *
     * @return boolean
     */
    protected function isSigningSchemaMacExtended(): bool
    {
        return $this->signingSchema == self::SIGNING_SCHEMA_MAC_EXTENDED;
    }

    /**
     * Is MAC_ADVANCE signing schema?
     *
     * @return boolean
     */
    protected function isSigningSchemaMacAdvanced(): bool
    {
        return $this->signingSchema == self::SIGNING_SCHEMA_MAC_ADVANCED;
    }

    /**
     * Is MAC_GENERAL signing schema?
     *
     * @return boolean
     */
    protected function isSigningSchemaMacGeneral(): bool
    {
        return $this->signingSchema == self::SIGNING_SCHEMA_MAC_GENERAL;
    }

    /**
     * Generate signature of data with private key
     *
     * @param array $data Данни върху които да генерира подписа.
     *
     * @return string
     * @throws SignatureException
     */
    protected function getPrivateSignature(array $data): string
    {
        $signature = $this->getSignatureSource($data);

        /*
         * sign signature
         */
        $privateKey = openssl_get_privatekey('file://' . $this->getPrivateKey(), $this->getPrivateKeyPassword());
        if (!$privateKey) {
            throw new SignatureException(openssl_error_string());
        }

        $openSignStatus = openssl_sign($signature, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$openSignStatus) {
            throw new SignatureException(openssl_error_string());
        }

        if (PHP_MAJOR_VERSION < 8) {
            /**
             * @deprecated in PHP 8.0
             * @note       The openssl_pkey_free() function is deprecated and no longer has an effect,
             * instead the OpenSSLAsymmetricKey instance is automatically destroyed if it is no
             * longer referenced.
             * @see        https://github.com/php/php-src/blob/master/UPGRADING#L397
             */
            openssl_pkey_free($privateKey);
        }

        return strtoupper(bin2hex($signature));
    }

    /**
     * Generate signature source
     *
     * @param array $data Data of signature.
     * @param boolean $isResponse Generate signature from response.
     *
     * @return string
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

        if ($this->isSigningSchemaMacGeneral()) {
            // Отново заради тъпото поле RFU (Reserved for Future Use)
            $signature .= '-';
        }

        return $signature;
    }

    /**
     * Get private key
     *
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * Set private key
     *
     * @param string $privateKeyPath Път до файла на частният ключ.
     * @param string|null $password Парола на частният ключ.
     *
     * @return BulBank
     */
    public function setPrivateKey(string $privateKeyPath, string $password = null): static
    {
        $this->privateKey = $privateKeyPath;

        if (!empty($password)) {
            $this->setPrivateKeyPassword($password);
        }

        return $this;
    }

    /**
     * Get private key password
     *
     * @return string|null
     */
    public function getPrivateKeyPassword(): ?string
    {
        return $this->privateKeyPassword;
    }

    /**
     * Set private key password
     *
     * @param string|null $privateKeyPassword Парола на частният ключ.
     *
     * @return BulBank
     */
    public function setPrivateKeyPassword(?string $privateKeyPassword): static
    {
        $this->privateKeyPassword = $privateKeyPassword;
        return $this;
    }

}
