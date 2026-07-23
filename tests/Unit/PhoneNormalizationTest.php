<?php

namespace Tests\Unit;

use App\Actions\Payments\ValidatePaymentRows;
use Tests\TestCase;

class PhoneNormalizationTest extends TestCase
{
    protected ValidatePaymentRows $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ValidatePaymentRows();
    }

    public function test_normalizes_07_format(): void
    {
        $this->assertEquals('254712345678', $this->validator->normalizePhone('0712345678'));
    }

    public function test_normalizes_01_format(): void
    {
        $this->assertEquals('254112345678', $this->validator->normalizePhone('0112345678'));
    }

    public function test_normalizes_plus_254_format(): void
    {
        $this->assertEquals('254712345678', $this->validator->normalizePhone('+254712345678'));
    }

    public function test_normalizes_254_format(): void
    {
        $this->assertEquals('254712345678', $this->validator->normalizePhone('254712345678'));
    }

    public function test_rejects_invalid_phone(): void
    {
        $this->assertNull($this->validator->normalizePhone('12345'));
    }

    public function test_rejects_empty_phone(): void
    {
        $this->assertNull($this->validator->normalizePhone(''));
    }

    public function test_strips_non_numeric_characters(): void
    {
        $this->assertEquals('254712345678', $this->validator->normalizePhone('+254-712-345-678'));
    }
}
