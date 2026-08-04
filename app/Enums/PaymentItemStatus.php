<?php

namespace App\Enums;

enum PaymentItemStatus: string
{
    case VALIDATED = 'validated';
    case INVALID = 'invalid';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case SUCCESSFUL = 'successful';
    case FAILED = 'failed';
    case TIMEOUT = 'timeout';
    case RETRYING = 'retrying';
    case SKIPPED = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::VALIDATED => 'Validated',
            self::INVALID => 'Invalid',
            self::QUEUED => 'Queued',
            self::PROCESSING => 'Processing',
            self::SUCCESSFUL => 'Successful',
            self::FAILED => 'Failed',
            self::TIMEOUT => 'Timeout',
            self::RETRYING => 'Retrying',
            self::SKIPPED => 'Skipped',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VALIDATED => 'emerald',
            self::INVALID => 'red',
            self::QUEUED => 'slate',
            self::PROCESSING => 'blue',
            self::SUCCESSFUL => 'emerald',
            self::FAILED => 'red',
            self::TIMEOUT => 'amber',
            self::RETRYING => 'orange',
            self::SKIPPED => 'stone',
        };
    }

    public function isPayable(): bool
    {
        return in_array($this, [self::VALIDATED, self::QUEUED, self::FAILED, self::TIMEOUT]);
    }
}
