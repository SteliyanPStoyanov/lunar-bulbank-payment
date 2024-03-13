<?php

namespace Lunar\BulBank\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Lunar\BulBank\Exceptions\ParameterValidationException;
use Lunar\BulBank\Exceptions\SignatureException;
use Lunar\BulBank\Services\SaleResponse;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;

final class WebhookController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|void
     * @throws ParameterValidationException
     * @throws SignatureException
     */
    public function __invoke(Request $request)
    {
        $saleResponse = (new SaleResponse())
            ->setPublicKey(base_path(config('bulbank.public_cer_path')))
            ->setSigningSchemaMacGeneral(); // use MAC_GENERAL

        $responseData = $saleResponse->getResponseData(false);
        Log::channel('bul-bank-log')->error("bul-bank" . json_encode($responseData));
        $cartId = str_replace("0", "", $responseData['ORDER']);
        if ($responseData['RC'] === '00') {

            $payment = Payments::driver('bulbank')->cart(Cart::find($cartId))->withData(array_merge([
                'ip' => app()->request->ip(),
                'accept' => app()->request->header('Accept'),
                'responseData' => $responseData
            ]))->authorize();

            if ($payment->success) {
                return redirect()->route('checkout-success.view');
            }
        } else {

            $payment = Payments::driver('bulbank')->cart(Cart::find($cartId))->withData(array_merge([
                'ip' => app()->request->ip(),
                'accept' => app()->request->header('Accept'),
                'responseData' => $responseData,
            ]))->cancel();

            if ($payment->success) {
                return redirect()->route('checkout-error.view', ['orderId' => $payment->orderId]);
            }
        }

    }
}
