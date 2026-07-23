<?php

namespace Tests\Unit;

use App\Actions\Payments\ValidatePaymentRows;
use Tests\TestCase;

class ValidatePaymentRowsTest extends TestCase
{
    protected ValidatePaymentRows $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ValidatePaymentRows();
    }

    public function test_valid_row_passes(): void
    {
        $result = $this->validator->validate([
            'employee_name' => 'John Mwangi',
            'mpesa_phone_number' => '254712345678',
            'payment_amount' => 15000,
        ]);

        $this->assertEmpty($result['errors']);
        $this->assertEquals('254712345678', $result['normalized_phone']);
        $this->assertEquals(15000.0, $result['amount']);
    }

    public function test_missing_employee_name(): void
    {
        $result = $this->validator->validate([
            'employee_name' => '',
            'mpesa_phone_number' => '254712345678',
            'payment_amount' => 15000,
        ]);

        $this->assertContains('Employee name is required.', $result['errors']);
    }

    public function test_missing_phone_number(): void
    {
        $result = $this->validator->validate([
            'employee_name' => 'John',
            'mpesa_phone_number' => '',
            'payment_amount' => 15000,
        ]);

        $this->assertContains('MPesa Phone Number is required.', $result['errors']);
    }

    public function test_invalid_phone_number(): void
    {
        $result = $this->validator->validate([
            'employee_name' => 'John',
            'mpesa_phone_number' => '12345',
            'payment_amount' => 15000,
        ]);

        $this->assertContains('Invalid MPesa Phone Number format.', $result['errors']);
    }

    public function test_missing_amount(): void
    {
        $result = $this->validator->validate([
            'employee_name' => 'John',
            'mpesa_phone_number' => '254712345678',
            'payment_amount' => '',
        ]);

        $this->assertContains('Payment amount is required.', $result['errors']);
    }

    public function test_non_numeric_amount(): void
    {
        $result = $this->validator->validate([
            'employee_name' => 'John',
            'mpesa_phone_number' => '254712345678',
            'payment_amount' => 'abc',
        ]);

        $this->assertContains('Payment amount must be numeric.', $result['errors']);
    }

    public function test_zero_amount(): void
    {
        $result = $this->validator->validate([
            'employee_name' => 'John',
            'mpesa_phone_number' => '254712345678',
            'payment_amount' => 0,
        ]);

        $this->assertContains('Payment amount must be greater than zero.', $result['errors']);
    }

    public function test_negative_amount(): void
    {
        $result = $this->validator->validate([
            'employee_name' => 'John',
            'mpesa_phone_number' => '254712345678',
            'payment_amount' => -100,
        ]);

        $this->assertContains('Payment amount must be greater than zero.', $result['errors']);
    }
}
