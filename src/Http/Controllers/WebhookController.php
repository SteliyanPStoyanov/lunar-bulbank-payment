<?php

namespace Lunar\BulBank\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SendingException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Services\SaleResponse;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;

final class WebhookController extends Controller
{
    /**
     * @throws SignatureException
     * @throws SendingException
     * @throws ParameterValidationException
     */
    public function __invoke(Request $request)
    {
        $saleResponse = (new SaleResponse())
            ->setPublicKey(base_path(config('bulbank.public_cer_path')))
            ->setSigningSchemaMacGeneral(); // use MAC_GENERAL

        $responseData = $saleResponse->getResponseData(false);

        if ($responseData['RC'] === 0) {
            $cartId = str_replace("0", "", $responseData['ORDER']);
            Payments::driver('bulbank')->cart(Cart::find($cartId))->withData(array_merge([
                'ip' => app()->request->ip(),
                'accept' => app()->request->header('Accept'),
                'ad' => $responseData
            ]))->authorize();
        } else {
            CartSession::forget();
            throw new SendingException(json_encode($responseData));
        }

    }
}
