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

    public function __invoke(Request $request)
    {
        $saleResponse = (new SaleResponse())
            ->setPublicKey(base_path(config('bulbank.public_cer_path')))
            ->setSigningSchemaMacGeneral(); // use MAC_GENERAL

        $responseData = $saleResponse->getResponseData(false);

        Log::channel('bul-bank-log')->error("bul-bank" . json_encode($responseData));

        if ($responseData['RC'] === '00') {
            return redirect()->route('checkout-success.view', ['responseData' => $responseData ,'payment' =>'bullbank']);
        } else {

            return redirect()->route('checkout-error.view', ['responseData' => $responseData ,'payment' =>'bullbank']);
        }

    }
}
