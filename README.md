# 9PAY Payment Gateway PHP SDK

<p align="left">
  <a href="https://packagist.org/packages/ninepay-gateway/rest-client-php">
    <img src="https://img.shields.io/packagist/v/ninepay-gateway/rest-client-php.svg?style=flat-square" alt="Latest Version">
  </a>
  <a href="https://github.com/ninepay-gateway/rest-client-php/actions">
    <img src="https://img.shields.io/badge/build-passing-brightgreen?style=flat-square" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/ninepay-gateway/rest-client-php">
    <img src="https://img.shields.io/packagist/dt/ninepay-gateway/rest-client-php.svg?style=flat-square" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/ninepay-gateway/rest-client-php">
    <img src="https://img.shields.io/packagist/l/ninepay-gateway/rest-client-php.svg?style=flat-square" alt="License">
  </a>
</p>

Official PHP SDK for integrating **9PAY Payment Gateway**.  
Supports **PHP Native**, **Laravel**, and **Lumen**.

## Features

The SDK currently supports:

-   Create payment request
-   Query transaction status
-   Verify webhook / callback signature
-   Refund transaction
-   Payer authentication for installment payments
-   Credit card authorization
-   Capture authorized payment
-   Reverse authorization
-   Strong typed request objects
-   Laravel & Lumen integration
-   OOP & SOLID compliant architecture

------------------------------------------------------------------------

## Table of Contents

-   Requirements
-   Installation
-   Configuration
  -   PHP Native
  -   Laravel
  -   Lumen
-   Usage
  -   Initialization
  -   Create Payment
  -   Query Transaction
  -   Verify Webhook
  -   Refund Transaction
  -   Payer Authentication
  -   Authorize Card Payment
  -   Capture Authorized Payment
  -   Reverse Authorization
-   Enums
-   License

------------------------------------------------------------------------

## Requirements

-   PHP \>= 7.4
-   Required extensions:
  -   json
  -   openssl

------------------------------------------------------------------------

## Installation

Install via Composer:

``` bash
composer require ninepay-gateway/rest-client-php
```

------------------------------------------------------------------------

## Configuration

### PHP Native

``` php
use NinePay\Config\NinePayConfig;
use NinePay\Gateways\NinePayGateway;

$config = new NinePayConfig(
    'MERCHANT_ID',
    'SECRET_KEY',
    'CHECKSUM_KEY',
    'SANDBOX' // or PRODUCTION
);

$gateway = new NinePayGateway($config);
```

You may also create configuration from array:

``` php
$config = NinePayConfig::fromArray([
    'merchant_id' => 'MID',
    'secret_key' => 'SECRET',
    'checksum_key' => 'CHECKSUM',
    'env' => 'SANDBOX',
]);
```

------------------------------------------------------------------------

### Laravel

Publish configuration file:

``` bash
php artisan vendor:publish --tag=ninepay-config
```

Then configure environment variables:

``` env
NINEPAY_MERCHANT_ID=your_merchant_id
NINEPAY_SECRET_KEY=your_secret_key
NINEPAY_CHECKSUM_KEY=your_checksum_key
NINEPAY_ENV=SANDBOX
```

After configuration, the gateway is automatically resolved via Laravel's
service container.

Usage requires only the Facade:

``` php
use NinePay\Facades\NinePay;

$response = NinePay::createPayment($request);
```

Example in controller:

``` php
public function pay()
{
    $request = new CreatePaymentRequest(
        'INV_' . time(),
        50000,
        'Payment Order',
        route('payment.return'),
        route('payment.cancel')
    );

    $response = NinePay::createPayment($request);

    return redirect($response->getData()['redirect_url']);
}
```

------------------------------------------------------------------------

### Lumen

Copy config file:

``` bash
cp vendor/ninepay-gateway/rest-client-php/config/ninepay.php config/ninepay.php
```

Register provider in `bootstrap/app.php`:

``` php
$app->register(NinePay\NinePayServiceProvider::class);
$app->configure('ninepay');
```

Enable facades:

``` php
$app->withFacades();
class_alias(NinePay\Facades\NinePay::class, 'NinePay');
```

------------------------------------------------------------------------

## Usage

### Initialization

PHP Native:

``` php
use NinePay\Config\NinePayConfig;
use NinePay\Gateways\NinePayGateway;

$config = new NinePayConfig('MID', 'SECRET', 'CHECKSUM', 'SANDBOX');
$gateway = new NinePayGateway($config);
```

Laravel:

``` php
$response = NinePay::createPayment($request);
```

------------------------------------------------------------------------

## Create Payment

``` php
use NinePay\Request\CreatePaymentRequest;
use NinePay\Enums\Currency;
use NinePay\Enums\Language;
use NinePay\Enums\TransactionType;

$request = new CreatePaymentRequest(
    'INV_' . time(),
    3100000,
    'Payment for Order',
    'https://site.com/return',
    'https://site.com/cancel'
);

$request
    ->withClientIp('127.0.0.1')
    ->withCurrency(Currency::VND)
    ->withLang(Language::VI)
    ->withTransactionType(TransactionType::INSTALLMENT)
    ->withExpiresTime(1440);

$response = $gateway->createPayment($request);
```

------------------------------------------------------------------------

## Query Transaction

``` php
$response = $gateway->inquiry('INV_123456');

if ($response->isSuccess()) {
    print_r($response->getData());
}
```

------------------------------------------------------------------------

## Verify Webhook

``` php
$result = $_POST['result'] ?? '';
$checksum = $_POST['checksum'] ?? '';

if ($gateway->verify($result, $checksum)) {

    $json = $gateway->decodeResult($result);
    $data = json_decode($json, true);

    $invoiceNo = $data['invoice_no'];
    $status = $data['status'];

    echo 'OK';
} else {
    http_response_code(400);
    echo 'Checksum Mismatch';
}
```

------------------------------------------------------------------------

## Refund Transaction

``` php
use NinePay\Request\CreateRefundRequest;
use NinePay\Enums\Currency;

$request = new CreateRefundRequest(
    'REF_' . time(),
    436271072913641,
    3100000,
    'Refund reason'
);

$request->withCurrency(Currency::VND)
        ->withBank(
            'BIDV',
            '1023020330000',
            'NGUYEN VAN A'
        );

$response = $gateway->refund($request);
```

------------------------------------------------------------------------

## Payer Authentication

``` php
use NinePay\Request\PayerAuthRequest;

$request = new PayerAuthRequest(
    'REQ_' . time(),
    5000000,
    'https://site.com/return'
);

$request->withInstallment(5000000, 'VCB', 12)
        ->withCard(
            '4456530000001005',
            'NGUYEN VAN A',
            '12',
            '27',
            '123'
        );

$response = $gateway->payerAuth($request);
```

------------------------------------------------------------------------

## Authorize Card Payment

``` php
use NinePay\Request\AuthorizeCardPaymentRequest;
use NinePay\Enums\Currency;

$request = new AuthorizeCardPaymentRequest(
    'REQ_' . time(),
    436271072913641,
    3100000,
    Currency::VND
);

$request->withCard(
    '4456530000001005',
    'NGUYEN VAN A',
    '12',
    '27',
    '123'
);

$response = $gateway->authorizeCardPayment($request);
```

------------------------------------------------------------------------

## Capture Authorized Payment

``` php
use NinePay\Request\CapturePaymentRequest;
use NinePay\Enums\Currency;

$request = new CapturePaymentRequest(
    'REQ_' . time(),
    436272499763441,
    20000,
    Currency::VND
);

$response = $gateway->capture($request);
```

------------------------------------------------------------------------

## Reverse Authorization

``` php
use NinePay\Request\ReverseCardPaymentRequest;

$request = new ReverseCardPaymentRequest(
    'REQ_' . time(),
    436272499763441,
    3100000,
    'VND'
);

$request->withCard(
    '4456530000001005',
    'NGUYEN VAN A',
    '12',
    '27',
    '123'
);

$response = $gateway->reverseCardPayment($request);
```

------------------------------------------------------------------------

## Enums

### Currency

Supported examples:

    VND, USD, EUR, JPY, AUD, ...

### Language

    VI, EN

### Transaction Type

    INSTALLMENT
    CARD_AUTHORIZATION

------------------------------------------------------------------------

## License

MIT License © 9Pay