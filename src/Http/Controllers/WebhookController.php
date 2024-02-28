<?php

namespace Lunar\BulBank\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class WebhookController extends Controller
{
    public function __invoke(Request $request)
    {
      dd($request);
    }
}
