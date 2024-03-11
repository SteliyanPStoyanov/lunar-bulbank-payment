<?php

namespace Lunar\BulBank\Repositories;


use Lunar\BulBank\Exceptions\DataMissingException;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Models\BulBank;

abstract class Response extends BulBank
{
    /**
     * @var boolean
     */
    private bool $dataIsVerified = false;

    /**
     * @var array
     */
    private array $responseData;

    /**
     * Get verified data by key        
     *
     * @return mixed
     * @throws SignatureException|ParameterValidationException|DataMissingException
     */
    public function getVerifiedData($key): mixed
    {

        if (!$this->dataIsVerified) {
            $this->verifyData();
        }

        $data = $this->getResponseData();

        if (!isset($data[$key])) {
            throw new DataMissingException($key . ' missing in verified response data');
        }

        return $data[$key];
    }

    /**
     * Verify data with public certificate
     *
     * @return void
     * @throws SignatureException|ParameterValidationException
     */
    protected function verifyData(): void
    {
        if ($this->dataIsVerified) {
            return;
        }

        $verifyingFields = $this->getVerifyingFields();

        $dataToVerify = [];

        /**
         * Response from borica
         */
        $responseFromBorica = $this->getResponseData(false);

        /*
         * Check required data
         */
        foreach (array_merge($verifyingFields, ['P_SIGN']) as $key) {
            if (!array_key_exists($key, $responseFromBorica)) {
                throw new ParameterValidationException($key . ' is missing in response data!');
            }
            if ($key != 'P_SIGN') {
                if ($key == 'CURRENCY' && empty($responseFromBorica[$key]) && $responseFromBorica['TRTYPE'] == 90) {
                    $responseFromBorica['CURRENCY'] = 'USD';
                }

                $dataToVerify[] = $responseFromBorica[$key];
            }
        }

        $this->verifyPublicSignature($dataToVerify, $responseFromBorica['P_SIGN']);

        $this->dataIsVerified = true;
    }

    /**
     * @return array
     */
    protected function getVerifyingFields(): array
    {
        return [
            'ACTION',
            'RC',
            'APPROVAL',
            'TERMINAL',
            'TRTYPE',
            'AMOUNT',
            'CURRENCY',
            'ORDER',
            'RRN',
            'INT_REF',
            'PARES_STATUS',
            'ECI',
            'TIMESTAMP',
            'NONCE',
        ];
    }

    /**
     * Get response data
     *
     * @note If response data is not set - set data to $_POST
     *
     * @param boolean $verify Verify data before return.
     *
     * @return array
     * @throws ParameterValidationException
     * @throws SignatureException
     */
    public function getResponseData(bool $verify = true): array
    {
        if (empty($this->responseData)) {
            $this->setResponseData($_POST);
        }

        if ($verify) {
            $this->verifyData();
        }

        return $this->responseData;
    }

    /**
     * Set response data
     *
     * @param array $responseData Response data from borica.
     *
     * @return Response
     */
    public function setResponseData(array $responseData): static
    {
        $this->dataIsVerified = false;
        $this->responseData = $responseData;
        return $this;
    }

    /**
     * Verify data with public certificate
     *
     * @param array  $data            Данни върху които да генерира подписа.
     * @param string $publicSignature Публичен подпис.
     *
     * @return void
     * @throws ParameterValidationException|SignatureException
     */
    protected function verifyPublicSignature(array $data, string $publicSignature): void
    {
        /*
         * generate signature
         */
        $signature = $this->getSignatureSource($data, true);

        /*
         * Open certificate file
         */
        $fp = fopen($this->getPublicKey(), "r");
        $publicKeyContent = fread($fp, filesize($this->getPublicKey()));
        fclose($fp);

        /*
         * get public key
         */
        $publicKey = openssl_get_publickey($publicKeyContent);
        if (!$publicKey) {
            throw new SignatureException('Open public key error: '.openssl_error_string());
        }

        /*
         * verifying
         */
        $verifyStatus = openssl_verify($signature, hex2bin($publicSignature), $publicKey, OPENSSL_ALGO_SHA256);
        if ($verifyStatus !== 1) {
            throw new SignatureException('Data signature verify error! Error: ' . openssl_error_string());
        }

        if (PHP_MAJOR_VERSION < 8) {
            /**
             * @deprecated in PHP 8.0
             * @note       The openssl_pkey_free() function is deprecated and no longer has an effect,
             * instead the OpenSSLAsymmetricKey instance is automatically destroyed if it is no
             * longer referenced.
             * @see        https://github.com/php/php-src/blob/master/UPGRADING#L397
             */
            openssl_pkey_free($publicKey);
        }
    }
}
