<p align="center">
  <img src="https://raw.githubusercontent.com/9Pay-company/payment-gateway-php/refs/heads/master/logo-readme.png?sanitize=true" width="500" alt="9PAY Logo">
</p>


[![Latest Version on Packagist](https://img.shields.io/packagist/v/ninepay-php/payment-gateway.svg?style=flat-square)](https://packagist.org/packages/ninepay-php/payment-gateway)
[![Tests](https://img.shields.io/badge/build-passing-brightgreen)](https://github.com/ninepay-php/payment-gateway/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/ninepay-php/payment-gateway.svg?style=flat-square)](https://packagist.org/packages/ninepay-php/payment-gateway)
[![License](https://img.shields.io/packagist/l/ninepay-php/payment-gateway.svg?style=flat-square)](https://packagist.org/packages/ninepay-php/payment-gateway)
[![Stars](https://img.shields.io/github/stars/badges/shields)](https://packagist.org/packages/ninepay-php/payment-gateway)

Official PHP library for integrating 9PAY payment gateway. Supports creating payment requests, querying transactions, and verifying webhooks/callbacks.

---

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Initialization](#initialization)
    - [Create Payment](#create-payment)
    - [Query Transaction](#query-transaction)
    - [Verify Webhook/Callback](#verify-webhookcallback)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Security](#security)
- [License](#license)

---

## Installation

Install the library via [Composer](https://getcomposer.org/):

```bash
composer require ninepay-php/payment-gateway
```

Requirements:
- PHP >= 7.4
- `json` and `openssl` extensions

## Configuration

You need identification information from the 9PAY Dashboard:

```php
$config = [
    'merchant_id'  => 'YOUR_MERCHANT_ID',  // Or client_id
    'secret_key'   => 'YOUR_SECRET_KEY',   // Used to sign API requests
    'checksum_key' => 'YOUR_CHECKSUM_KEY', // Used to verify Webhooks
    'env'          => 'SANDBOX',           // SANDBOX (default) | PRODUCTION
];
```

## Usage

### Initialization

You can use `PaymentManager` or the `Facade` (if in a supported environment or used directly).

**Using Facade:**

```php
use NinePay\Facades\Payment;

$payment = new Payment($config);
```

**Using Manager:**

```php
use NinePay\PaymentManager;

$manager = new PaymentManager($config);
$gateway = $manager->getGateway();
```

### Create Payment

Use the `CreatePaymentRequest` DTO to prepare data:

```php
use NinePay\Support\CreatePaymentRequest;

$request = new CreatePaymentRequest(
    'INV123456',               // Invoice ID (invoice_no/request_code)
    '50000',                   // Amount (VND)
    'Payment for order #123',  // Description
    'https://your-site.com/callback', // Back URL (optional)
    'https://your-site.com/thanks'    // Return URL (optional)
);

$response = $payment->createPayment($request);

if ($response->isSuccess()) {
    $redirectUrl = $response->getData()['redirect_url'];
    // Redirect customer
    header('Location: ' . $redirectUrl);
    exit;
} else {
    echo "Error: " . $response->getMessage();
}
```

### Query Transaction

Check the status of a transaction via 9PAY `transactionId` or your invoice ID:

```php
$response = $payment->inquiry('9PAY_TRANSACTION_ID');

if ($response->isSuccess()) {
    $data = $response->getData();
    print_r($data);
}
```

### Verify Webhook/Callback

When 9PAY sends notifications to your server, verify the validity:

```php
$payload = [
    'result'   => $_POST['result']   ?? '',
    'checksum' => $_POST['checksum'] ?? '',
];

if ($payment->verify($payload)) {
    // Checksum is valid, decode the data
    $decodedJson = $payment->getGateway()->decodeResult($payload['result']);
    $data = json_decode($decodedJson, true);
    
    // Process order based on $data['status']
} else {
    // Data is invalid or tampered with
    http_response_code(400);
}
```

## Error Handling

The library throws exceptions belonging to `NinePay\Exceptions\PaymentException` when an error occurs.

```php
use NinePay\Exceptions\PaymentException;

try {
    $response = $payment->createPayment($request);
} catch (PaymentException $e) {
    // Handle configuration or connection errors
    error_log($e->getMessage());
}
```

## Testing

Run the test suite using PHPUnit:

```bash
composer test
```

## Security

If you discover any security issues, please email `hotro@9pay.vn` instead of creating a public Issue.

## License

Released under the [MIT](LICENSE) license. Copyright belongs to **9Pay**.
