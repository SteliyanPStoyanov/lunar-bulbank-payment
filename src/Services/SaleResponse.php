<?php

namespace Lunar\BulBank\Services;

use Lunar\BulBank\Exceptions\DataMissingException;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Interfaces\ResponseInterface;
use Lunar\BulBank\Repositories\Response;

/**
 * Class Sale
 *
 */
class SaleResponse extends Response implements ResponseInterface
{
    /**
     * Is success payment?
     *
     * @return boolean
     * @throws DataMissingException
     * @throws ParameterValidationException
     * @throws SignatureException
     */
    public function isSuccessful(): bool
    {
        return $this->getResponseCode() === '00';
    }

    /**
     * Get response code - value of 'RC' field
     *
     * @return string
     * @throws SignatureException|ParameterValidationException|DataMissingException
     */
    public function getResponseCode(): string
    {
        return $this->getVerifiedData('RC');
    }
}
