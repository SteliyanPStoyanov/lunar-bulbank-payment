<?php

namespace Lunar\BulBank\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lunar\BulBank\Services\SaleResponse;

final class WebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $saleResponse = (new SaleResponse())
            ->setPublicKey(base_path(config('bulbank.public_cer_path')))
            ->setSigningSchemaMacGeneral(); // use MAC_GENERAL

        dd($request, $saleResponse, $saleResponse->getResponseData(false));

    }
}
