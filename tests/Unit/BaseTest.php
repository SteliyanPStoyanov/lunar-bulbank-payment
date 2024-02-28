<?php

namespace Lunar\BulBank\Tests\Unit;

use Lunar\BulBank\Services\SaleRequest;
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

}
