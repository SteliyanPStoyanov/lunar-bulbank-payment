<?php
namespace Lunar\BulBank\Interfaces;

interface RequestInterface
{
    public function getData(): array;

    public function send();

    public function generateForm();

    public function generateSignature(): string;

    public function validateRequiredParameters(): void;
}
