
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

This package allows you to:
- Create payment requests with strictly typed parameters.
- Query transaction status.
- Verify webhook / callback data.
- Integrate easily using OOP, SOLID & Facade pattern.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
    - [Using NinePayConfig (PHP Native)](#using-ninepayconfig-php-native)
    - [Laravel](#laravel)
    - [Lumen](#lumen)
- [Usage](#usage)
    - [Initialization](#initialization)
    - [Create Payment](#create-payment)
    - [Query Transaction](#query-transaction)
    - [Verify Webhook / Callback](#verify-webhook--callback)
    - [Refund Transaction](#refund-transaction)
- [Enums & Constants](#enums--constants)
- [License](#license)

---

## Requirements

- PHP **>= 7.4**
- Extensions:
    - `json`
    - `openssl`

---

## Installation

Install the SDK via Composer:

```bash
composer require ninepay-gateway/rest-client-php
```

---

## Configuration

### Using NinePayConfig (PHP Native)

You strictly define your configuration using the `NinePay\Config\NinePayConfig` class.

```php
use NinePay\Config\NinePayConfig;

$config = new NinePayConfig(
    'YOUR_MERCHANT_ID',
    'YOUR_SECRET_KEY',
    'YOUR_CHECKSUM_KEY',
    'SANDBOX' // or 'PRODUCTION'
);

// Or create from array
$config = NinePayConfig::fromArray([
    'merchant_id'  => 'YOUR_MERCHANT_ID',
    'secret_key'   => 'YOUR_SECRET_KEY',
    'checksum_key' => 'YOUR_CHECKSUM_KEY',
    'env'          => 'SANDBOX',
]);
```

### Laravel

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ninepay-config
```

Then strictly defined in your `.env` file:

```env
NINEPAY_MERCHANT_ID=your_merchant_id
NINEPAY_SECRET_KEY=your_secret_key
NINEPAY_CHECKSUM_KEY=your_checksum_key
NINEPAY_ENV=SANDBOX
```

### Lumen

Copy the configuration file:

```bash
mkdir -p config
cp vendor/ninepay-gateway/rest-client-php/config/ninepay.php config/ninepay.php
```

Register the provider in `bootstrap/app.php`:

```php
$app->register(NinePay\NinePayServiceProvider::class);
$app->configure('ninepay');
```

And configure aliases:
```php
$app->withFacades();
class_alias(NinePay\Facades\NinePay::class, 'NinePay');
```

---

## Usage

### Initialization

**PHP Native:**

```php
use NinePay\Gateways\NinePayGateway;
use NinePay\Config\NinePayConfig;

$config = new NinePayConfig('MID', 'SECRET', 'CHECKSUM', 'SANDBOX');
$gateway = new NinePayGateway($config);
```

**Laravel / Lumen:**

The gateway is automatically configured via the service container. You can use the `NinePay` facade to interact with the gateway.

```php
use NinePay\Facades\NinePay;

// You can call methods directly on the Facade
// $response = NinePay::createPayment($request);
```

---

### Create Payment

The SDK uses `CreatePaymentRequest` with a fluent interface for setting parameters.

```php
use NinePay\Enums\Currency;
use NinePay\Enums\Language;
use NinePay\Enums\PaymentMethod;
use NinePay\Enums\TransactionType;
use NinePay\Exceptions\PaymentException;
use NinePay\Request\CreatePaymentRequest;

try {
    // 1. Create Request with required fields
    $request = new CreatePaymentRequest(
        'INV_' . time(),       // Invoice No
        50000,                 // Amount
        'Payment for Order 1', // Description
        'https://site.com/callback', // Back/Cancel URL
        'https://site.com/return'    // Return URL
    );

    // 2. Add optional parameters using fluent setters
    $request->withMethod(PaymentMethod::ATM_CARD)
            ->withClientIp('127.0.0.1')
            ->withCurrency(Currency::VND)
            ->withLang(Language::VI)
            ->withTransactionType(TransactionType::INSTALLMENT)
            ->withExpiresTime(1440); // Minutes

    // 3. Send Request
    $response = $gateway->createPayment($request);

    if ($response->isSuccess()) {
        $redirectUrl = $response->getData()['redirect_url'];
        header('Location: ' . $redirectUrl);
        exit;
    } else {
        echo "Error: " . $response->getMessage();
    }
} catch (\InvalidArgumentException $e) {
    // Validation error in Request creation
    echo "Invalid Input: " . $e->getMessage();
} catch (PaymentException $e) {
    // API or Gateway error
    echo "Payment Error: " . $e->getMessage();
}
```

---

### Query Transaction

Check the status of a transaction using the Invoice No (Transaction ID).

```php
$response = $gateway->inquiry('INV_123456');

if ($response->isSuccess()) {
    print_r($response->getData());
} else {
    echo $response->getMessage();
}
```

---

### Verify Webhook / Callback

Verify the data received from 9Pay (IPN).

```php
// Assume data comes from $_POST
$result = $_POST['result'] ?? '';
$checksum = $_POST['checksum'] ?? '';

if ($gateway->verify($result, $checksum)) {
    // Signature valid, decode the data
    $jsonResult = $gateway->decodeResult($result);
    $data = json_decode($jsonResult, true);
    
    // Process order...
    $invoiceNo = $data['invoice_no'];
    $status = $data['status']; // 'success', 'failure'
    
    echo 'OK';
} else {
    // Invalid signature
    http_response_code(400);
    echo 'Checksum Mismatch';
}
```

---

### Payer Authentication

Authenticate payer information for installment payments.

```php
use NinePay\Request\PayerAuthRequest;

// 1. Initialize Request
$request = new PayerAuthRequest(
    'REQ_' . time(),       // Request ID
    5000000,               // Amount (Min 3,000,000)
    'https://site.com/return' // Return URL
);

// 2. Add Installment & Card info
$request->withInstallment(5000000, 'VCB', 12)
    ->withCard(
        '1234567890123456', // Card Number
        'NGUYEN VAN A',     // Hold Name
        12,                 // Exp Month
        25,                 // Exp Year (2 digits)
        '123'               // CVV
    );

// 3. Send Payer Auth Request
$response = $gateway->payerAuth($request);

if ($response->isSuccess()) {
    echo "Auth Success: " . $response->getMessage();
    print_r($response->getData());
} else {
    echo "Auth Failed: " . $response->getMessage();
}
```

---

### Authorize (Credit Card)

Authorize a credit card payment.

```php
use NinePay\Request\AuthorizeCardPaymentRequest;

// 1. Initialize Request
$request = new AuthorizeCardPaymentRequest(
    'REQ_' . time(),    // Request ID
    123456789,          // Order Code
    5000000,            // Amount
    'VND'
);

->withCard(
    '1234567890123456', // Card Number
    'NGUYEN VAN A',     // Hold Name
    12,                 // Exp Month
    25,                 // Exp Year (2 digits)
    '123'               // CVV
);

// 2. Send Authorize Request
$response = $gateway->authorizeCardPayment($request);

if ($response->isSuccess()) {
    echo "Authorize Success: " . $response->getMessage();
    print_r($response->getData());
} else {
    echo "Authorize Failed: " . $response->getMessage();
}
```

---

### Refund Transaction

Create a refund request for a successful transaction.

```php
use NinePay\Request\CreateRefundRequest;
use NinePay\Enums\Currency;

// 1. Initialize Request
$request = new CreateRefundRequest(
    'REF_' . time(),       // Refund Request Code (Merchant side)
    436271072913641,       // Original Transaction No (from 9Pay)
    3100000,               // Refund Amount
    'Refund reason'        // Description
);

// 2. Add banking info (Optional/Required depending on method)
$request->withCurrency(Currency::VND)
    ->withBank(
        'BIDV',            // Bank Code
        '1023020330000',   // Account Number
        'NGUYEN VAN A'     // Account Name
    );

// 3. Send Refund Request
$response = $gateway->refund($request);

if ($response->isSuccess()) {
    echo "Refund Success: " . $response->getMessage();
    print_r($response->getData());
} else {
    echo "Refund Failed: " . $response->getMessage();
}
```

---

## Enums & Constants

The SDK provides Enums to avoid magic strings and ensure validity.

### `NinePay\Enums\PaymentMethod`
- `ATM_CARD`
- `CREDIT_CARD`
- `9PAY`
- `COLLECTION`
- `APPLE_PAY`
- `vNPAY_PORTONE`
- `ZALOPAY_WALLET`
- `GOOGLE_PAY`
- `QR_PAY`
- `BUY_NOW_PAY_LATER`

### `NinePay\Enums\Currency`
- `VND`, `USD`, `IDR`, `EUR`, `GBP`, `CNY`, `JPY`, `AUD`, `KRW`, `CAD`, `HKD`, `INR`

### `NinePay\Enums\Language`
- `VI` (Vietnamese)
- `EN` (English)

### `NinePay\Enums\TransactionType`
- `INSTALLMENT`
- `CARD_AUTHORIZATION`

---

## License

MIT License © 9Pay
