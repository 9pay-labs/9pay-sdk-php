<?php
declare(strict_types=1);

namespace NinePay\Gateways;

use NinePay\Contracts\PaymentGatewayInterface;
use NinePay\Contracts\RequestInterface;
use NinePay\Contracts\ResponseInterface;
use NinePay\Exceptions\InvalidConfigException;
use NinePay\Support\BasicResponse;
use NinePay\Utils\Environment;
use NinePay\Utils\HttpClient;
use NinePay\Utils\MessageBuilder;
use NinePay\Utils\Signature;
use NinePay\Utils\UnicodeFormat;

/**
 * Class NinePayGateway
 * 
 * Provides methods to interact with the 9Pay payment gateway.
 */
class NinePayGateway implements PaymentGatewayInterface
{
    /** @var array<string,mixed> */
    private array $config;
    /** @var string Merchant ID */
    private string $clientId;
    /** @var string Secret key used for signing */
    private string $secretKey;
    /** @var string Key used for checksum verification */
    private string $checksumKey;
    /** @var string API endpoint URL */
    private string $endpoint;
    /** @var HttpClient HTTP Client */
    private HttpClient $http;

    /**
     * NinePayGateway constructor.
     *
     * @param array<string,mixed> $config Configuration including merchant_id, secret_key, checksum_key, and env.
     * @param HttpClient|null $http
     * @throws InvalidConfigException When required configuration is missing.
     */
    public function __construct(array $config, ?HttpClient $http = null)
    {
        $this->config = $config;
        $this->clientId = (string)($config['merchant_id'] ?? $config['client_id'] ?? '');
        $this->secretKey = (string)($config['secret_key'] ?? '');
        $this->checksumKey = (string)($config['checksum_key'] ?? '');
        $env = (string)($config['env'] ?? 'SANDBOX');
        $this->endpoint = Environment::endpoint($env);
        $this->http = $http ?? new HttpClient();

        if ($this->clientId === '' || $this->secretKey === '' || $this->checksumKey === '') {
            throw new InvalidConfigException('NinePay config requires merchant_id, secret_key, checksum_key');
        }
    }

    /**
     * Create a payment request and get the redirect URL.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function createPayment(RequestInterface $request): ResponseInterface
    {
        $data = $request->toArray();

        $requestCode = (string)($data['request_code'] ?? $data['invoice_no'] ?? '');
        $amount = (string)($data['amount'] ?? '');
        $description = (string)($data['description'] ?? '');
        $backUrl = isset($data['back_url']) ? (string)$data['back_url'] : '';
        $returnUrl = isset($data['return_url']) ? (string)$data['return_url'] : '';

        if ($requestCode === '' || $amount === '' || $description === '') {
            return new BasicResponse(false, [], 'Missing required fields: request_code, amount, description');
        }

        $time = (string)time();
        $payload = [
            'merchantKey' => $this->clientId,
            'time' => $time,
            'invoice_no' => $requestCode,
            'amount' => $amount,
            'description' => $description,
        ];
        if ($backUrl !== '') { $payload['back_url'] = $backUrl; }
        if ($returnUrl !== '') { $payload['return_url'] = $returnUrl; }

        $message = MessageBuilder::instance()
            ->with($time, $this->endpoint . '/payments/create', 'POST')
            ->withParams($payload)
            ->build();

        $signature = Signature::sign($message, $this->secretKey);
        $httpData = [
            'baseEncode' => base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE)),
            'signature' => $signature,
        ];
        $redirectUrl = $this->endpoint . '/portal?' . http_build_query($httpData);

        return new BasicResponse(true, ['redirect_url' => $redirectUrl], 'OK');
    }

    /**
     * Query transaction status.
     *
     * @param string $transactionId Transaction ID or invoice ID to query.
     * @return ResponseInterface
     */
    public function inquiry(string $transactionId): ResponseInterface
    {
        $time = (string)time();
        $message = MessageBuilder::instance()
            ->with($time, $this->endpoint . '/v2/payments/' . $transactionId . '/inquire', 'GET')
            ->withParams([])
            ->build();

        $signature = Signature::sign($message, $this->secretKey);
        $headers = [
            'Date' => $time,
            'Authorization' => 'Signature Algorithm=HS256,Credential=' . $this->clientId . ',SignedHeaders=,Signature=' . $signature,
        ];

        $res = $this->http->get($this->endpoint . '/v2/payments/' . $transactionId . '/inquire', $headers);
        $ok = $res['status'] >= 200 && $res['status'] < 300;
        return new BasicResponse($ok, is_array($res['body']) ? $res['body'] : ['raw' => $res['body']], (string)($res['body']['message'] ?? ''));
    }

    /**
     * Verify IPN/Return signature from 9Pay.
     *
     * @param array $payload Data received from 9Pay.
     * @return bool
     */
    public function verify(array $payload): bool
    {
        $result = isset($payload['result']) ? (string)$payload['result'] : '';
        $checksum = isset($payload['checksum']) ? (string)$payload['checksum'] : '';
        if ($result === '' || $checksum === '') {
            return false;
        }
        $hashChecksum = strtoupper(hash('sha256', $result . $this->checksumKey));
        return hash_equals($hashChecksum, $checksum);
    }

    /**
     * Decode result data when verify() is successful.
     *
     * @param string $result Base64 encoded result string.
     * @return string JSON result string after decoding.
     */
    public function decodeResult(string $result): string
    {
        return UnicodeFormat::urlsafeB64Decode($result);
    }
}
