<?php

namespace Lunar\BulBank\Tests\Unit;

use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Services\SaleRequest;
use Lunar\BulBank\Tests\TestCase;

class SaleRequestTest extends TestCase
{
    /**
     * @return void
     * @throws ParameterValidationException|SignatureException
     */
    public function testSignature()
    {
        $sale = (new SaleRequest())
            ->setAmount(1)
            ->setCurrency('BGN')
            ->setOrder(145659)
            ->setDescription('Детайли плащане.')
            ->setMerchantGMT('+03')
            ->setMerchantUrl('https://test.com')
            ->setEmailAddress('test@test.com')
            ->setBackRefUrl('https://test.com/back-ref-url')
            ->setTerminalID(self::TERMINAL_ID)
            ->setMerchantId(self::MERCHANT_ID)
            ->setPrivateKey(__DIR__ . '/certificates/development.key')
            ->setPrivateKeyPassword('test')
            ->setSignatureTimestamp('20201013115715')
            ->setNonce('FC8AC36A9FDADCB6127D273CD15DAEC3')
            ->setAdCustBorOrderId('test');

        $this->assertEquals(
            '0C4C1A61B15979A75E438A6547B0A1F5194FEC081C6E8C26764C9F9C6867D3B159BF42E3F1C83077F34439D981CBF9B9360D66733081667E4569FE59277D89303D010185A0B0317FC94CED3D163FE432DD5B559938323E601157F50EB08F4DCD0B7892661724CD08CF42376BDBBC5769D6EEBBC6F03513F2C6EB3B788C8DEFCD6784D08CDFB0613B6D3547E0B5B106AEC6C7E2AA151A98D0DFB84B10B16669C297D89F35DADCB4F8429E285B0FAB6374B561A6E2B432DC1552EBB730B7D59C57767248304DB2E2D7E5B57B60F6D351D6CCAF301F486CBDD5DDD2C5D3FA0DDC949982B0A385C8F49854B26714A2443720E1DB577A09270462B74CECC20E98EC57',
            $sale->generateSignature()
        );
    }

    /**
     * @return void
     * @throws ParameterValidationException|SignatureException
     */
    public function testSignatureMacGeneral()
    {
        $sale = (new SaleRequest())
            ->setAmount(1)
            ->setCurrency('BGN')
            ->setOrder(145659)
            ->setDescription('Детайли плащане.')
            ->setMerchantGMT('+03')
            ->setMerchantUrl('https://test.com')
            ->setEmailAddress('test@test.com')
            ->setBackRefUrl('https://test.com/back-ref-url')
            ->setTerminalID(self::TERMINAL_ID)
            ->setMerchantId(self::MERCHANT_ID)
            ->setPrivateKey(__DIR__ . '//certificates/development.key')
            ->setPrivateKeyPassword('test')
            ->setSignatureTimestamp('20201013115715')
            ->setNonce('FC8AC36A9FDADCB6127D273CD15DAEC3')
            ->setAdCustBorOrderId('test');

        $this->assertEquals(
            '0C4C1A61B15979A75E438A6547B0A1F5194FEC081C6E8C26764C9F9C6867D3B159BF42E3F1C83077F34439D981CBF9B9360D66733081667E4569FE59277D89303D010185A0B0317FC94CED3D163FE432DD5B559938323E601157F50EB08F4DCD0B7892661724CD08CF42376BDBBC5769D6EEBBC6F03513F2C6EB3B788C8DEFCD6784D08CDFB0613B6D3547E0B5B106AEC6C7E2AA151A98D0DFB84B10B16669C297D89F35DADCB4F8429E285B0FAB6374B561A6E2B432DC1552EBB730B7D59C57767248304DB2E2D7E5B57B60F6D351D6CCAF301F486CBDD5DDD2C5D3FA0DDC949982B0A385C8F49854B26714A2443720E1DB577A09270462B74CECC20E98EC57',
            $sale->generateSignature()
        );
    }

    /**
     * @throws ParameterValidationException|SignatureException
     */
    public function testDataMacGeneral()
    {
        $saleData = (new SaleRequest())
            ->setAmount(1)
            ->setCurrency('BGN')
            ->setOrder(145659)
            ->setDescription('Детайли плащане.')
            ->setMerchantGMT('+03')
            ->setMerchantUrl('https://test.com')
            ->setBackRefUrl('https://test.com/back-ref-url')
            ->setTerminalID(self::TERMINAL_ID)
            ->setMerchantId(self::MERCHANT_ID)
            ->setPrivateKey(__DIR__ . '/certificates/development.key')
            ->setPrivateKeyPassword('test')
            ->setSignatureTimestamp('20201013115715')
            ->setNonce('FC8AC36A9FDADCB6127D273CD15DAEC3')
            ->setEmailAddress('test@test.com')
            ->setAdCustBorOrderId('test')
            ->getData();

        $this->assertEquals([
            'TRTYPE' => 1,
            'COUNTRY' => null,
            'CURRENCY' => 'BGN',
            'MERCH_GMT' => '+03',
            'ORDER' => '145659',
            'AMOUNT' => '1.00',
            'DESC' => 'Детайли плащане.',
            'TIMESTAMP' => '20201013115715',
            'TERMINAL' => self::TERMINAL_ID,
            'MERCH_URL' => 'https://test.com',
            'MERCH_NAME' => null,
            'EMAIL' => 'test@test.com',
            'BACKREF' => 'https://test.com/back-ref-url',
            'AD.CUST_BOR_ORDER_ID' => 'test',
            'ADDENDUM' => 'AD,TD',
            'NONCE' => 'FC8AC36A9FDADCB6127D273CD15DAEC3',
            'MERCHANT' => self::MERCHANT_ID,
            'P_SIGN' => '0C4C1A61B15979A75E438A6547B0A1F5194FEC081C6E8C26764C9F9C6867D3B159BF42E3F1C83077F34439D981CBF9B9360D66733081667E4569FE59277D89303D010185A0B0317FC94CED3D163FE432DD5B559938323E601157F50EB08F4DCD0B7892661724CD08CF42376BDBBC5769D6EEBBC6F03513F2C6EB3B788C8DEFCD6784D08CDFB0613B6D3547E0B5B106AEC6C7E2AA151A98D0DFB84B10B16669C297D89F35DADCB4F8429E285B0FAB6374B561A6E2B432DC1552EBB730B7D59C57767248304DB2E2D7E5B57B60F6D351D6CCAF301F486CBDD5DDD2C5D3FA0DDC949982B0A385C8F49854B26714A2443720E1DB577A09270462B74CECC20E98EC57'
        ], $saleData);
    }

    /**
     * @throws ParameterValidationException|SignatureException
     */
    public function testData()
    {
        $saleData = (new SaleRequest())
            ->setAmount(1)
            ->setCurrency('BGN')
            ->setOrder(145659)
            ->setDescription('Детайли плащане.')
            ->setMerchantGMT('+03')
            ->setMerchantUrl('https://test.com')
            ->setBackRefUrl('https://test.com/back-ref-url')
            ->setTerminalID(self::TERMINAL_ID)
            ->setMerchantId(self::MERCHANT_ID)
            ->setPrivateKey(__DIR__ . '/certificates/development.key')
            ->setPrivateKeyPassword('test')
            ->setSignatureTimestamp('20201013115715')
            ->setNonce('FC8AC36A9FDADCB6127D273CD15DAEC3')
            ->setEmailAddress('test@test.com')
            ->setAdCustBorOrderId('test')
            ->getData();

        $this->assertEquals([
            'TRTYPE' => 1,
            'COUNTRY' => null,
            'CURRENCY' => 'BGN',
            'MERCH_GMT' => '+03',
            'ORDER' => '145659',
            'AMOUNT' => '1.00',
            'DESC' => 'Детайли плащане.',
            'TIMESTAMP' => '20201013115715',
            'TERMINAL' => self::TERMINAL_ID,
            'MERCH_URL' => 'https://test.com',
            'MERCH_NAME' => null,
            'EMAIL' => 'test@test.com',
            'BACKREF' => 'https://test.com/back-ref-url',
            'AD.CUST_BOR_ORDER_ID' => 'test',
            'ADDENDUM' => 'AD,TD',
            'NONCE' => 'FC8AC36A9FDADCB6127D273CD15DAEC3',
            'MERCHANT' => self::MERCHANT_ID,
            'P_SIGN' => '0C4C1A61B15979A75E438A6547B0A1F5194FEC081C6E8C26764C9F9C6867D3B159BF42E3F1C83077F34439D981CBF9B9360D66733081667E4569FE59277D89303D010185A0B0317FC94CED3D163FE432DD5B559938323E601157F50EB08F4DCD0B7892661724CD08CF42376BDBBC5769D6EEBBC6F03513F2C6EB3B788C8DEFCD6784D08CDFB0613B6D3547E0B5B106AEC6C7E2AA151A98D0DFB84B10B16669C297D89F35DADCB4F8429E285B0FAB6374B561A6E2B432DC1552EBB730B7D59C57767248304DB2E2D7E5B57B60F6D351D6CCAF301F486CBDD5DDD2C5D3FA0DDC949982B0A385C8F49854B26714A2443720E1DB577A09270462B74CECC20E98EC57'
        ], $saleData);
    }

    /**
     * @return void
     * @throws ParameterValidationException|SignatureException
     */
    public function testBackRefValidation()
    {
        $this->expectException(ParameterValidationException::class);

        (new SaleRequest())
            ->setBackRefUrl('wrong url value');
    }
}
