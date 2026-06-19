<?php
declare(strict_types=1);

class StripeClient
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function createCheckoutSession(array $params): array
    {
        return $this->request('POST', '/v1/checkout/sessions', $this->buildQuery($params));
    }

    public function createRefund(string $paymentIntentId, int $amountCents): array
    {
        return $this->request('POST', '/v1/refunds', $this->buildQuery([
            'payment_intent' => $paymentIntentId,
            'amount' => $amountCents
        ]));
    }
    
    public function retrieveCheckoutSession(string $sessionId): array
    {
        return $this->request('GET', '/v1/checkout/sessions/' . urlencode($sessionId), '');
    }

    public function verifyWebhookSignature(string $payload, string $sigHeader, string $secret): bool
    {
        // $sigHeader looks like: t=1492774577,v1=5257a869e7ecebeda32affa62cdca3fa51cad7e77a0e56ff536d0ce8e108d8bd,v0=6ffbb59b2300aae63f272406069a9788598b792a944a07abc816e08ce1a1fc1f
        $parts = explode(',', $sigHeader);
        $timestamp = '';
        $signatures = [];

        foreach ($parts as $part) {
            $split = explode('=', trim($part), 2);
            if (count($split) === 2) {
                if ($split[0] === 't') {
                    $timestamp = $split[1];
                } elseif ($split[0] === 'v1') {
                    $signatures[] = $split[1];
                }
            }
        }

        if ($timestamp === '' || empty($signatures)) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function request(string $method, string $endpoint, string $body): array
    {
        $ch = curl_init('https://api.stripe.com' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/x-www-form-urlencoded'
        ];
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        
        if ($statusCode >= 400) {
            throw new Exception('Stripe API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }
        
        return $data ?: [];
    }

    private function buildQuery(array $params, ?string $prefix = null): string
    {
        $query = [];
        foreach ($params as $k => $v) {
            $key = $prefix === null ? $k : $prefix . '[' . $k . ']';
            if (is_array($v)) {
                $query[] = $this->buildQuery($v, $key);
            } else {
                $query[] = urlencode((string)$key) . '=' . urlencode((string)$v);
            }
        }
        return implode('&', $query);
    }
}
