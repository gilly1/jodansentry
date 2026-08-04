<?php

namespace App\Http\Controllers\Mpesa;

use App\Actions\Mpesa\HandleB2CTimeoutCallback;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaTimeoutController extends Controller
{
    public function b2cTimeout(Request $request, HandleB2CTimeoutCallback $action): JsonResponse
    {
        Log::channel('mpesa')->info('B2C Timeout callback received', ['data' => $request->all()]);

        $action->execute($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function accountBalanceTimeout(Request $request): JsonResponse
    {
        Log::channel('mpesa')->info('Account Balance Timeout', ['data' => $request->all()]);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function transactionStatusTimeout(Request $request): JsonResponse
    {
        Log::channel('mpesa')->info('Transaction Status Timeout', ['data' => $request->all()]);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function reversalTimeout(Request $request): JsonResponse
    {
        Log::channel('mpesa')->info('Reversal Timeout', ['data' => $request->all()]);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }
}
