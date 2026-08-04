<?php

namespace App\Services\Mpesa;

use Illuminate\Support\Facades\Log;

class MpesaSecurityCredential
{
    public function generate(string $account): string
    {
        $resolver = app(MpesaAccountResolver::class);
        $config = $resolver->resolve($account);
        $env = config('mpesa.env', 'sandbox');

        $certPath = config("mpesa.certificates.{$env}");
        $password = $config['initiator_password'];

        if (!file_exists($certPath)) {
            throw new \RuntimeException("M-Pesa certificate not found at: {$certPath}");
        }

        $certContent = file_get_contents($certPath);

        // Try PEM format first
        $publicKey = openssl_pkey_get_public($certContent);

        if (!$publicKey) {
            // Try DER format
            $pemContent = "-----BEGIN CERTIFICATE-----\n"
                . chunk_split(base64_encode($certContent), 64, "\n")
                . "-----END CERTIFICATE-----\n";
            $publicKey = openssl_pkey_get_public($pemContent);
        }

        if (!$publicKey) {
            throw new \RuntimeException('Failed to load M-Pesa certificate');
        }

        openssl_public_encrypt($password, $encrypted, $publicKey);

        return base64_encode($encrypted);
    }
}
