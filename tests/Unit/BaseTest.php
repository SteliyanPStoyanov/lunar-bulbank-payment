<?php

namespace Lunar\BulBank\Tests\Unit;

use Lunar\BulBank\Models\BulBank;
use Lunar\BulBank\Services\ReversalRequest;
use Lunar\BulBank\Services\ReversalResponse;
use Lunar\BulBank\Services\SaleRequest;
use Lunar\BulBank\Services\SaleResponse;
use Lunar\BulBank\Services\StatusCheckRequest;
use Lunar\BulBank\Services\StatusCheckResponse;
use Lunar\BulBank\Tests\TestCase;


class BaseTest extends TestCase
{
    /**
     * @return void
     */
    public function testEnvironments()
    {
        $saleData = new SaleRequest();

        //init
        $this->assertTrue($saleData->isProduction());
        $this->assertFalse($saleData->isDevelopment());

        //to dev
        $saleData->inDevelopment();
        $this->assertFalse($saleData->isProduction());
        $this->assertTrue($saleData->isDevelopment());

        $saleData->setEnvironment(false);
        $this->assertFalse($saleData->isProduction());
        $this->assertTrue($saleData->isDevelopment());

        //to prod
        $saleData->inProduction();
        $this->assertTrue($saleData->isProduction());
        $this->assertFalse($saleData->isDevelopment());

        $saleData->setEnvironment(true);
        $this->assertTrue($saleData->isProduction());
        $this->assertFalse($saleData->isDevelopment());
    }

    public function testDefaultSigningSchema()
    {
        $this->assertTrue((new SaleRequest())->getSigningSchema() === BulBank::SIGNING_SCHEMA_MAC_GENERAL);
        $this->assertTrue((new SaleResponse())->getSigningSchema() === BulBank::SIGNING_SCHEMA_MAC_GENERAL);
        $this->assertTrue((new StatusCheckRequest())->getSigningSchema() === BulBank::SIGNING_SCHEMA_MAC_GENERAL);
        $this->assertTrue((new StatusCheckResponse())->getSigningSchema() === BulBank::SIGNING_SCHEMA_MAC_GENERAL);
        $this->assertTrue((new ReversalRequest())->getSigningSchema() === BulBank::SIGNING_SCHEMA_MAC_GENERAL);
        $this->assertTrue((new ReversalResponse())->getSigningSchema() === BulBank::SIGNING_SCHEMA_MAC_GENERAL);
    }

}
