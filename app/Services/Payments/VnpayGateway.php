<?php

namespace App\Services\Payments;

class VnpayGateway
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function createPaymentUrl(string $orderCode, int $amount, string $orderInfo, string $ipAddr): string
    {
        $vnpUrl       = (string) $this->config['payment_url'];
        $vnpReturnUrl = (string) $this->config['return_url'];
        $vnpTmnCode   = (string) $this->config['tmn_code'];
        $vnpHashSecret= (string) $this->config['hash_secret'];
        $vnpBankCode  = (string) ($this->config['bank_code'] ?? '');

        if ($ipAddr === '::1') {
            $ipAddr = '127.0.0.1';
        }

        $vnpAmount    = (int) $amount * 100;
        $vnpOrderInfo = $orderInfo;
        $vnpOrderType = 'billpayment';

        $inputData = [
            'vnp_Version'    => '2.1.0',
            'vnp_TmnCode'    => $vnpTmnCode,
            'vnp_Amount'     => $vnpAmount,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => $ipAddr,
            'vnp_Locale'     => 'vn',
            'vnp_OrderInfo'  => $vnpOrderInfo,
            'vnp_OrderType'  => $vnpOrderType,
            'vnp_ReturnUrl'  => $vnpReturnUrl,
            'vnp_TxnRef'     => $orderCode,
        ];

        if ($vnpBankCode !== '') {
            $inputData['vnp_BankCode'] = $vnpBankCode;
        }

        ksort($inputData);

        $hashParts  = [];
        $queryParts = [];

        foreach ($inputData as $key => $value) {
            $hashParts[]  = urlencode($key) . '=' . urlencode((string) $value);
            $queryParts[] = urlencode($key) . '=' . urlencode((string) $value);
        }

        $hashDataStr = implode('&', $hashParts);
        $queryStr    = implode('&', $queryParts);

        $vnpSecureHash = hash_hmac('sha512', $hashDataStr, $vnpHashSecret);

        return $vnpUrl . '?' . $queryStr . '&vnp_SecureHash=' . $vnpSecureHash;
    }
}
