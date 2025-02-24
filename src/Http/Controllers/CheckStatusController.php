<?php

namespace Lunar\BulBank\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lunar\BulBank\Services\StatusCheckRequest;
use Lunar\BulBank\Enums\TransactionType;

final class CheckStatusController extends Controller
{
    public function __invoke($order)
    {

        $statusCheckRequest = (new StatusCheckRequest())
            ->setSigningSchemaMacGeneral()
            ->inProduction()
            ->setPublicKey(base_path(config('bulbank.public_cer_path')))
            ->setTerminalID(config('bulbank.terminal_id'))
            ->setMerchantId(config('bulbank.merchant_id'))
            ->setOrder($order)
            ->setOriginalTransactionType(TransactionType::SALE())
            ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
            ->setPrivateKeyPassword(config('bulbank.private_key_pass')); // transaction type

        $statusCheckResponse = $statusCheckRequest->send();

// get data from borica response
        $verifiedResponseData = $statusCheckResponse->getResponseData(false);
        dd($verifiedResponseData);
    }
}
