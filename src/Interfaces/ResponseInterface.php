<?php

namespace Lunar\BulBank\Interfaces;

interface ResponseInterface
{
    public function getResponseData(): array;

    public function getVerifiedData(string $key): mixed;
}
