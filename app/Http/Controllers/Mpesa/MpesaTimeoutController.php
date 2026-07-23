<?php

namespace App\Http\Controllers\Mpesa;

use App\Actions\Mpesa\HandleB2CTimeoutCallback;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MpesaTimeoutController extends Controller
{
    public function store(Request $request, HandleB2CTimeoutCallback $handler): JsonResponse
    {
        $callbackSecret = config('mpesa.callback_secret');

        if ($callbackSecret && $request->query('secret') !== $callbackSecret) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Unauthorized'], 401);
        }

        $handler->handle($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
