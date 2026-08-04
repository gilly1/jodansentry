<?php

namespace App\Actions\Mpesa;

use App\Models\MpesaApiLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class HandleBalanceResultCallback
{
    public function execute(array $data): void
    {
        $result = $data['Result'] ?? $data;

        $originatorConversationId = $result['OriginatorConversationID'] ?? null;
        $conversationId = $result['ConversationID'] ?? null;
        $resultCode = (string) ($result['ResultCode'] ?? '');
        $resultDesc = $result['ResultDesc'] ?? '';

        MpesaApiLog::create([
            'direction' => 'callback',
            'endpoint' => '/account-balance/result',
            'payload' => $data,
            'masked_payload' => $data,
        ]);

        if ($resultCode !== '0') {
            Log::channel('mpesa')->warning('Balance query failed', [
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'originator_conversation_id' => $originatorConversationId,
            ]);

            SystemSetting::setValue('mpesa_balance_error', $resultDesc);
            SystemSetting::setValue('mpesa_balance_updated_at', now()->toIso8601String());

            return;
        }

        $params = $result['ResultParameters']['ResultParameter'] ?? [];
        $balanceString = null;
        $completedTime = null;

        foreach ($params as $param) {
            if ($param['Key'] === 'AccountBalance') {
                $balanceString = $param['Value'];
            }
            if ($param['Key'] === 'BOCompletedTime') {
                $completedTime = $param['Value'];
            }
        }

        $parsedAccounts = $this->parseBalanceString($balanceString);

        SystemSetting::setValue('mpesa_balance_data', json_encode($parsedAccounts));
        SystemSetting::setValue('mpesa_balance_raw', $balanceString);
        SystemSetting::setValue('mpesa_balance_updated_at', now()->toIso8601String());
        SystemSetting::setValue('mpesa_balance_conversation_id', $conversationId);
        SystemSetting::setValue('mpesa_balance_error', null);

        Log::channel('mpesa')->info('Balance result processed', [
            'originator_conversation_id' => $originatorConversationId,
            'conversation_id' => $conversationId,
            'accounts' => $parsedAccounts,
            'completed_time' => $completedTime,
        ]);
    }

    /**
     * Parse the M-Pesa balance string into structured data.
     *
     * Format: "AccountName|Currency|Available|Actual|Reserved|Uncleared&..."
     */
    private function parseBalanceString(?string $balanceString): array
    {
        if (empty($balanceString)) {
            return [];
        }

        $accounts = [];

        foreach (explode('&', $balanceString) as $accountStr) {
            $parts = explode('|', $accountStr);

            if (count($parts) >= 4) {
                $accounts[] = [
                    'account' => $parts[0] ?? '',
                    'currency' => $parts[1] ?? 'KES',
                    'available' => (float) ($parts[2] ?? 0),
                    'actual' => (float) ($parts[3] ?? 0),
                    'reserved' => (float) ($parts[4] ?? 0),
                    'uncleared' => (float) ($parts[5] ?? 0),
                ];
            }
        }

        return $accounts;
    }
}
