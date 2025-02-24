<?php

namespace Lunar\BulBank\RequestType;

use Lunar\BulBank\Exceptions\SendingException;

class Direct extends RequestType
{
    /**
     * @var false|resource
     */
    private $ch;

    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * @return boolean|string
     * @throws SendingException
     */
    public function send(): bool|string
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->getData()));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($this->ch);
        if (curl_error($this->ch)) {
            throw new SendingException(curl_error($this->ch));
        }
        curl_close($this->ch);

        return $response;
    }

    public function generateForm(): array
    {
        return $this->getData();
    }
}