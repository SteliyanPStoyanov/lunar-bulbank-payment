<?php

namespace Lunar\BulBank\Interfaces;

/**
 * Interface ResponseInterface
 *
 * @package VenelinIliev\Borica3ds
 */
interface ResponseInterface
{
    /**
     * @return array
     */
    public function getResponseData();

    /**
     * @param string $key Data key.
     *
     * @return mixed
     */
    public function getVerifiedData($key);
}
