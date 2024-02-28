<?php
namespace Lunar\BulBank\Tests\Unit;

use Lunar\BulBank\Exceptions\DataMissingException;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SendingException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Models\TransactionType;
use Lunar\BulBank\Services\StatusCheckRequest;
use Lunar\BulBank\Services\StatusCheckResponse;
use Lunar\BulBank\Tests\TestCase;


class StatusCheckRequestTest extends TestCase
{
    /**
     * @return void
     * @throws ParameterValidationException
     * @throws SignatureException
     */
    public function testSigning()
    {
        $statusCheckRequest = (new StatusCheckRequest())
           ->setPrivateKey(__DIR__ . '/certificates/development.key')
            ->setPublicKey(__DIR__ . '/certificates/V5402041_20240226_D.csr')
            ->setTerminalID(self::TERMINAL_ID)
            ->setOrder('115233')
            ->setOriginalTransactionType(TransactionType::SALE())
            ->setNonce('622CAAA8BF20C5A21A917DCB8401C337');

        $data = $statusCheckRequest->getData();

        $this->assertEquals(
            '931C852502F8F3370CD553BD2FC0AF59C7E42A99E37DF5160ED99D8806787A344FC243920BEE43F8D11F331E6A736FCCE81DAE279342C872AAE7988305CC4F1883B7D8C3F8F300322CDD930D989CD27B10E014C498BF4F5073F519FE9B9FE401E82CA9F8D631B9618D1E8174B9B1E2CC288C228DB8DB519CF095AD0F664FC86C6ACF6BE391FCF226AA92C4E6B3A9186276F83840378100DD12DF39A22141AF15D27DCEDDA6F30BF574A2DBF6A0CA5EDD2BC0D923F70D513F00B82079704538F62270EE87C3B23E73C0B68F750C6EAF3C9089DAC4EB15A5F6FAB5EDA3C9D3353B2DAC7ED9084E2172B0694D9B24AAA784F56A721FD4EEC06FB91EC819F9D87B80',
            $data['P_SIGN']
        );
    }

    /**
     * @throws ParameterValidationException
     * @throws SignatureException
     * @throws SendingException
     * @throws DataMissingException
     */
    public function testSend()
    {
        $statusCheckRequest = (new StatusCheckRequest())
            ->inDevelopment()
            ->setPrivateKey(__DIR__ . '/certificates/development.key')
            ->setPublicKey(__DIR__ . '/certificates/V5402041_20240226_D.csr')
            ->setMerchantId(self::MERCHANT_ID)
            ->setTerminalID(self::TERMINAL_ID)
            ->setOrder('115233')
            ->setOriginalTransactionType(TransactionType::SALE())
            ->setNonce('622CAAA8BF20C5A21A917DCB8401C337');

        $statusCheckResponse = $statusCheckRequest->send();
dd($statusCheckResponse->getResponseData());
        $this->assertEquals('3', $statusCheckResponse->getVerifiedData('ACTION'));
        $this->assertEquals('-24', $statusCheckResponse->getVerifiedData('RC'));
        $this->assertEquals('90', $statusCheckResponse->getVerifiedData('TRTYPE'));
        $this->assertEquals('115233', $statusCheckResponse->getVerifiedData('ORDER'));
        $this->assertEquals('622CAAA8BF20C5A21A917DCB8401C337', $statusCheckResponse->getVerifiedData('NONCE'));
    }

    /**
     * @throws ParameterValidationException
     * @throws SignatureException
     */
    public function testResponseMacGeneral()
    {
        $this->markTestSkipped('Да се провери защо не верифицира добре подписа!');

        $post = [
            'ACTION' => 0,
            'RC' => '00',
            'APPROVAL' => 'S78952',
            'TERMINAL' => self::TERMINAL_ID,
            'TRTYPE' => TransactionType::TRANSACTION_STATUS_CHECK,
            'AMOUNT' => '1.00',
            'CURRENCY' => 'BGN',
            'ORDER' => '114233',
            'RRN' => '029001254078',
            'INT_REF' => '4C9B34468610CF9F',
            'PARES_STATUS' => 'Y',
            'ECI' => '05',
            'TIMESTAMP' => '20201016084515',
            'NONCE' => '7A9A2E5CD173AF3F69A87F06E1F602ED',
            'P_SIGN' => '931C852502F8F3370CD553BD2FC0AF59C7E42A99E37DF5160ED99D8806787A344FC243920BEE43F8D11F331E6A736FCCE81DAE279342C872AAE7988305CC4F1883B7D8C3F8F300322CDD930D989CD27B10E014C498BF4F5073F519FE9B9FE401E82CA9F8D631B9618D1E8174B9B1E2CC288C228DB8DB519CF095AD0F664FC86C6ACF6BE391FCF226AA92C4E6B3A9186276F83840378100DD12DF39A22141AF15D27DCEDDA6F30BF574A2DBF6A0CA5EDD2BC0D923F70D513F00B82079704538F62270EE87C3B23E73C0B68F750C6EAF3C9089DAC4EB15A5F6FAB5EDA3C9D3353B2DAC7ED9084E2172B0694D9B24AAA784F56A721FD4EEC06FB91EC819F9D87B80',

        ];

        $rc = (new StatusCheckResponse())
            ->setPublicKey(__DIR__ . '/certificates/V5402041_20240226_D.csr')
            ->setResponseData($post)
            ->getResponseData('RC');

        $this->assertEquals('00', $rc);

    }
}
