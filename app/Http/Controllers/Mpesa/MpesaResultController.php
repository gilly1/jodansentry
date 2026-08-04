<?php

namespace App\Http\Controllers\Mpesa;

use App\Actions\Mpesa\HandleB2CResultCallback;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaResultController extends Controller
{
    public function b2cResult(Request $request, HandleB2CResultCallback $action): JsonResponse
    {
        Log::channel('mpesa')->info('B2C Result callback received', ['data' => $request->all()]);

        $action->execute($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function accountBalanceResult(Request $request): JsonResponse
    {
        Log::channel('mpesa')->info('Account Balance Result', ['data' => $request->all()]);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function transactionStatusResult(Request $request): JsonResponse
    {
        Log::channel('mpesa')->info('Transaction Status Result', ['data' => $request->all()]);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function reversalResult(Request $request): JsonResponse
    {
        Log::channel('mpesa')->info('Reversal Result', ['data' => $request->all()]);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }
}
