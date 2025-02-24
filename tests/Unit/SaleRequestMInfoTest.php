<?php
namespace Lunar\BulBank\Tests\Unit;

use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Services\SaleRequest;
use Lunar\BulBank\Tests\TestCase;

class SaleRequestMInfoTest extends TestCase
{
    /**
     * @throws ParameterValidationException
     */
    public function testGetMInfo()
    {
        $request = new  SaleRequest();
        $request->setMInfo([
            'cardholderName' => 'John Doe',
            'email' => 'johndoe@example.com'
        ]);
        $expected = base64_encode(json_encode([
            'cardholderName' => 'John Doe',
            'email' => 'johndoe@example.com'
        ]));
        $this->assertEquals($expected, $request->getMInfo());
    }

    /**
     * @throws ParameterValidationException
     */
    public function testMInfoWithInvalidEmail()
    {
        $this->expectException(ParameterValidationException::class);

        $request = new SaleRequest();
        $request->setMInfo([
            'cardholderName' => 'John Doe',
            'email' => 'invalid',
        ]);
    }

    /**
     * @throws ParameterValidationException
     */
    public function testMInfoWithInvalidCardholderNameLength()
    {
        $this->expectException(ParameterValidationException::class);

        $request = new SaleRequest();
        $request->setMInfo([
            'cardholderName' => str_repeat('a', 46),
            'email' => 'johndoe@example.com',
        ]);
    }

    /**
     * @throws ParameterValidationException
     */
    public function testMInfoWithMissingRequiredFields()
    {
        $this->expectException(ParameterValidationException::class);

        $request = new SaleRequest();
        $request->setMInfo(['email' => 'johndoe@example.com']);
    }

    /**
     * @throws ParameterValidationException
     */
    public function testMInfoWithInvalidMobilePhoneStructure()
    {
        $this->expectException(ParameterValidationException::class);

        $request = new SaleRequest();
        $request->setMInfo([
            'cardholderName' => 'John Doe',
            'mobilePhone' => ['invalid' => 'invalid'],
        ]);
    }
}
