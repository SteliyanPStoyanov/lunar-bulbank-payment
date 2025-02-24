<?php

namespace Lunar\BulBank\Repositories;

use Lunar\BulBank\Enums\Action;
use Lunar\BulBank\Enums\ResponseCode;
use Lunar\BulBank\Enums\TransactionType;
use Lunar\BulBank\Exceptions\DataMissingException;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Models\BulBank;
use Lunar\BulBank\Services\ReversalResponse;
use Lunar\BulBank\Services\SaleResponse;
use Lunar\BulBank\Services\StatusCheckResponse;

abstract class Response extends BulBank
{

    protected bool $dataIsVerified = false;

    protected array $responseData;

    /**
     * @throws DataMissingException
     */
    public static function determineResponse(array $responseData = null): Response
    {
        if (is_null($responseData)) {
            $responseData = $_POST;
        }

        if (empty($responseData['TRTYPE'])) {
            throw new DataMissingException('TRTYPE missing or empty in response data');
        }

        $response = match ($responseData['TRTYPE']) {
            TransactionType::SALE => new SaleResponse(),
            TransactionType::REVERSAL => new ReversalResponse(),
            TransactionType::TRANSACTION_STATUS_CHECK => new StatusCheckResponse(),
            default => throw new DataMissingException('Unknown transaction type'),
        };

        return $response->setResponseData($responseData);
    }

    /**
     * @throws ParameterValidationException
     * @throws DataMissingException
     * @throws SignatureException
     */
    public function getVerifiedData(string $key): mixed
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
     * @throws ParameterValidationException
     * @throws SignatureException
     */
    protected function verifyData(): void
    {
        if ($this->dataIsVerified) {
            return;
        }

        $verifyingFields = $this->getVerifyingFields();

        $dataToVerify = [];

        $responseFromBorica = $this->getResponseData(false);

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
     * @throws SignatureException
     * @throws ParameterValidationException
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

    public function setResponseData(array $responseData): Response
    {
        $this->dataIsVerified = false;
        $this->responseData = $responseData;
        return $this;
    }

    /**
     * @throws ParameterValidationException
     * @throws SignatureException
     */
    protected function verifyPublicSignature(array $data, string $publicSignature): void
    {

        $signature = $this->getSignatureSource($data, true);

        $fp = fopen($this->getPublicKey(), "r");
        $publicKeyContent = fread($fp, filesize($this->getPublicKey()));
        fclose($fp);

        $publicKey = openssl_get_publickey($publicKeyContent);
        if (!$publicKey) {
            throw new SignatureException('Open public key error: '.openssl_error_string());
        }

        $verifyStatus = openssl_verify($signature, hex2bin($publicSignature), $publicKey, OPENSSL_ALGO_SHA256);
        if ($verifyStatus !== 1) {
            throw new SignatureException('Data signature verify error! Error: ' . openssl_error_string());
        }
    }

    /**
     * @throws ParameterValidationException
     * @throws DataMissingException
     */
    public function isSuccessful(): bool
    {
        return $this->getResponseCode() === ResponseCode::SUCCESS &&
            $this->getAction() === Action::SUCCESS;
    }

    /**
     * @throws ParameterValidationException
     * @throws SignatureException
     * @throws DataMissingException
     */
    public function getResponseCode(): string
    {
        return $this->getVerifiedData('RC');
    }

    /**
     * @throws SignatureException
     * @throws DataMissingException
     * @throws ParameterValidationException
     */
    public function getAction(): string
    {
        return $this->getVerifiedData('ACTION');
    }
}
