<?php

return [
    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE'),
        'access_key'   => env('MOMO_ACCESS_KEY'),
        'secret_key'   => env('MOMO_SECRET_KEY'),
        'test_env'     => (bool) env('MOMO_TEST_ENV', true),
        'redirect_url' => env('MOMO_REDIRECT_URL'),
        'ipn_url'      => env('MOMO_IPN_URL'),
        'endpoint'     => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
    ],

    'vnpay' => [
        'tmn_code'    => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'return_url'  => env('VNPAY_RETURN_URL'),
        'payment_url' => env('VNPAY_PAYMENT_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'bank_code'   => env('VNPAY_BANK_CODE'),
    ],
];
