<?php

namespace App\Http\Controllers\Mpesa;

use App\Actions\Mpesa\HandleB2CResultCallback;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MpesaResultController extends Controller
{
    public function store(Request $request, HandleB2CResultCallback $handler): JsonResponse
    {
        $callbackSecret = config('mpesa.callback_secret');

        if ($callbackSecret && $request->query('secret') !== $callbackSecret) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Unauthorized'], 401);
        }

        $handler->handle($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
