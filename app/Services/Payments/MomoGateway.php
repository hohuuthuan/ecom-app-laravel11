<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MomoGateway
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function createPaymentUrl(string $orderCode, int $amount, string $orderInfo): string
    {
        $endpoint = $this->config['endpoint'] ?? 'https://test-payment.momo.vn/v2/gateway/api/create';

        $partnerCode = (string) $this->config['partner_code'];
        $accessKey   = (string) $this->config['access_key'];
        $secretKey   = (string) $this->config['secret_key'];
        $redirectUrl = (string) $this->config['redirect_url'];
        $ipnUrl      = (string) $this->config['ipn_url'];

        $requestId   = (string) now()->timestamp;
        $requestType = 'payWithATM';
        $extraData   = '';

        $rawHash = 'accessKey=' . $accessKey
            . '&amount=' . $amount
            . '&extraData=' . $extraData
            . '&ipnUrl=' . $ipnUrl
            . '&orderId=' . $orderCode
            . '&orderInfo=' . $orderInfo
            . '&partnerCode=' . $partnerCode
            . '&redirectUrl=' . $redirectUrl
            . '&requestId=' . $requestId
            . '&requestType=' . $requestType;

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'partnerName' => config('app.name', 'Book Shop'),
            'storeId'     => 'BookShopStore',
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderCode,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature,
        ];

        $response = Http::timeout(10)->post($endpoint, $payload);

        if (!$response->ok()) {
            throw new RuntimeException('MoMo API error: ' . $response->body());
        }

        $data = $response->json();

        if (!is_array($data) || empty($data['payUrl'])) {
            throw new RuntimeException('MoMo response invalid.');
        }

        return (string) $data['payUrl'];
    }
}
