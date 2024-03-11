<?php

namespace Lunar\BulBank\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lunar\BulBank\Services\StatusCheckRequest;
use Lunar\BulBank\Models\TransactionType;

final class CheckStatusController extends Controller
{
    public function __invoke($order)
    {

        $statusCheckRequest = (new StatusCheckRequest())
            ->inDevelopment()
            ->setPrivateKey(base_path(config('bulbank.private_key_path')), config('bulbank.private_key_pass'))
            ->setPrivateKeyPassword(config('bulbank.private_key_pass'))
            ->setPublicKey(base_path(config('bulbank.public_cer_path')))
            ->setTerminalID(config('bulbank.terminal_id'))
            ->setMerchantId(config('bulbank.merchant_id'))
            ->setOrder($order)
            ->setSigningSchemaMacGeneral()
            ->setBackRefUrl(url('back-ref-url')) // optional / required for development
            ->setOriginalTransactionType(TransactionType::SALE()); // transaction type

        $statusCheckResponse = $statusCheckRequest->send();

// get data from borica response
$verifiedResponseData = $statusCheckResponse->getResponseData();
        dd( $verifiedResponseData);
    }
}
