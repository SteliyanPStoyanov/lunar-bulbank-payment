<?php

namespace Lunar\BulBank\RequestType;

use Lunar\BulBank\Repositories\Response;

abstract class RequestType
{
    private string $url = '';

    private array $data = [];

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): RequestType
    {
        $this->url = $url;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): RequestType
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return Response|string|void
     */
    abstract public function send();

    abstract public function generateForm(): array|string;
}