<?php

namespace App\Enums;

enum PaymentBatchStatus: string
{
    case UPLOADED = 'uploaded';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SCHEDULED = 'scheduled';
    case PROCESSING = 'processing';
    case SUCCESSFUL = 'successful';
    case PARTIALLY_SUCCESSFUL = 'partially_successful';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::UPLOADED => 'Uploaded',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::SCHEDULED => 'Scheduled',
            self::PROCESSING => 'Processing',
            self::SUCCESSFUL => 'Successful',
            self::PARTIALLY_SUCCESSFUL => 'Partially Successful',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UPLOADED => 'slate',
            self::PENDING_APPROVAL => 'amber',
            self::APPROVED => 'sky',
            self::REJECTED => 'red',
            self::SCHEDULED => 'indigo',
            self::PROCESSING => 'blue',
            self::SUCCESSFUL => 'emerald',
            self::PARTIALLY_SUCCESSFUL => 'orange',
            self::FAILED => 'red',
            self::CANCELLED => 'stone',
        };
    }
}
