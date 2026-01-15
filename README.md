
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

- Create payment requests
- Query transaction status
- Verify webhook / callback data
- Integrate easily using OOP, SOLID & Facade pattern

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
    - [PHP Native](#php-native)
    - [Laravel](#laravel)
    - [Lumen](#lumen)
- [Usage](#usage)
    - [Initialization](#initialization)
    - [Create Payment](#create-payment)
    - [Query Transaction](#query-transaction)
    - [Verify Webhook / Callback](#verify-webhook--callback)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Security](#security)
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

### PHP Native

```php
$config = [
    'merchant_id'  => 'YOUR_MERCHANT_ID',
    'secret_key'   => 'YOUR_SECRET_KEY',
    'checksum_key' => 'YOUR_CHECKSUM_KEY',
    'env'          => 'SANDBOX',
];
```

---

### Laravel

```bash
php artisan vendor:publish --tag=ninepay-config
```

```env
NINEPAY_MERCHANT_ID=your_merchant_id
NINEPAY_SECRET_KEY=your_secret_key
NINEPAY_CHECKSUM_KEY=your_checksum_key
NINEPAY_ENV=SANDBOX
```

---

### Lumen

```bash
mkdir -p config
cp vendor/ninepay-gateway/rest-client-php/config/ninepay.php config/ninepay.php
```

```php
$app->register(NinePay\NinePayServiceProvider::class);
$app->configure('ninepay');
```

```php
$app->withFacades();
class_alias(NinePay\Facades\NinePay::class, 'NinePay');
```

---

## Usage

### Initialization

```php
use NinePay\PaymentManager;

$manager = new PaymentManager($config);
$gateway = $manager->getGateway();
```

```php
use NinePay;

$gateway = NinePay::getGateway();
```

---

### Create Payment

```php
use NinePay\Support\CreatePaymentRequest;

$request = new CreatePaymentRequest(
    'INV123456',
    '50000',
    'Payment for order #123',
    'https://your-site.com/callback',
    'https://your-site.com/return'
);

$response = $gateway->createPayment($request);

if ($response->isSuccess()) {
    header('Location: ' . $response->getData()['redirect_url']);
    exit;
}

echo $response->getMessage();
```

---

### Query Transaction

```php
$response = $gateway->inquiry('9PAY_TRANSACTION_ID');

if ($response->isSuccess()) {
    print_r($response->getData());
}
```

---

### Verify Webhook / Callback

```php
$result = $_POST['result'] ?? '';
$checksum = $_POST['checksum'] ?? '';

if ($gateway->verify($result, $checksum)) {
    $decoded = $gateway->decodeResult($result);
    $data = json_decode($decoded, true);
} else {
    http_response_code(400);
}
```

---

## Error Handling

```php
use NinePay\Exceptions\PaymentException;

try {
    $gateway->createPayment($request);
} catch (PaymentException $e) {
    logger()->error($e->getMessage());
}
```

---

## Testing

```bash
composer test
```

---

## Security

Please report security issues to **hotro@9pay.vn**.

---

## License

MIT License © 9Pay
