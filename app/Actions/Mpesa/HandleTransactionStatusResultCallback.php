<?php

namespace App\Actions\Mpesa;

use App\Models\MpesaApiLog;
use App\Models\PaymentItem;
use Illuminate\Support\Facades\Log;

class HandleTransactionStatusResultCallback
{
    public function execute(array $data): void
    {
        $result = $data['Result'] ?? $data;

        $originatorConversationId = $result['OriginatorConversationID'] ?? null;
        $conversationId = $result['ConversationID'] ?? null;
        $transactionId = $result['TransactionID'] ?? null;
        $resultCode = (string) ($result['ResultCode'] ?? '');
        $resultDesc = $result['ResultDesc'] ?? '';

        MpesaApiLog::create([
            'direction' => 'callback',
            'endpoint' => '/transaction-status/result',
            'payload' => $data,
            'masked_payload' => $data,
        ]);

        $params = $result['ResultParameters']['ResultParameter'] ?? [];
        $parsed = $this->parseResultParameters($params);

        // Try to find and update the matching PaymentItem
        $paymentItem = $this->findPaymentItem($originatorConversationId, $conversationId, $transactionId, $parsed);

        if ($paymentItem) {
            $this->updatePaymentItem($paymentItem, $resultCode, $resultDesc, $parsed, $data);
        }

        Log::channel('mpesa')->info('Transaction status result processed', [
            'originator_conversation_id' => $originatorConversationId,
            'conversation_id' => $conversationId,
            'transaction_id' => $transactionId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'parsed_params' => $parsed,
            'payment_item_found' => $paymentItem !== null,
        ]);
    }

    private function findPaymentItem(?string $originatorConversationId, ?string $conversationId, ?string $transactionId, array $parsed): ?PaymentItem
    {
        $receiptNo = $parsed['receipt_no'] ?? $transactionId;

        // First try by transaction receipt
        if ($receiptNo) {
            $item = PaymentItem::where('mpesa_transaction_receipt', $receiptNo)->first();
            if ($item) {
                return $item;
            }
        }

        // Try by originator conversation ID
        if ($originatorConversationId) {
            $item = PaymentItem::where('mpesa_originator_conversation_id', $originatorConversationId)->first();
            if ($item) {
                return $item;
            }
        }

        // Try by conversation ID
        if ($conversationId) {
            $item = PaymentItem::where('mpesa_conversation_id', $conversationId)->first();
            if ($item) {
                return $item;
            }
        }

        return null;
    }

    private function updatePaymentItem(PaymentItem $item, string $resultCode, string $resultDesc, array $parsed, array $rawData): void
    {
        $updates = [
            'mpesa_result_code' => $resultCode,
            'mpesa_result_description' => $resultDesc,
            'callback_payload' => $rawData,
        ];

        if (!empty($parsed['receipt_no']) && empty($item->mpesa_transaction_receipt)) {
            $updates['mpesa_transaction_receipt'] = $parsed['receipt_no'];
        }

        $item->update($updates);
    }

    private function parseResultParameters(array $params): array
    {
        $mapped = [];

        $keyMap = [
            'ReceiptNo' => 'receipt_no',
            'Amount' => 'amount',
            'TransactionStatus' => 'transaction_status',
            'FinalisedTime' => 'finalised_time',
            'InitiatedTime' => 'initiated_time',
            'CreditPartyName' => 'credit_party_name',
            'DebitPartyName' => 'debit_party_name',
            'DebitPartyCharges' => 'debit_party_charges',
            'DebitAccountType' => 'debit_account_type',
            'ReasonType' => 'reason_type',
            'TransactionReason' => 'transaction_reason',
            'Conversation ID' => 'conversation_id',
            'Originator Conversation ID' => 'originator_conversation_id',
        ];

        foreach ($params as $param) {
            $key = $param['Key'] ?? '';
            $value = $param['Value'] ?? null;

            if (isset($keyMap[$key])) {
                $mapped[$keyMap[$key]] = $value;
            }
        }

        return $mapped;
    }
}
