<?php
declare(strict_types=1);

namespace NinePay\Gateways;

use JsonException;
use NinePay\Config\NinePayConfig;
use NinePay\Contracts\PaymentGatewayInterface;
use NinePay\Contracts\ResponseInterface;
use NinePay\Request\CreatePaymentRequest;
use NinePay\Request\CreateRefundRequest;
use NinePay\Request\PayerAuthRequest;
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
     * @param \NinePay\Config\NinePayConfig $config
     * @param HttpClient|null $http
     */
    public function __construct(NinePayConfig $config, ?HttpClient $http = null)
    {
        $this->clientId = $config->getMerchantId();
        $this->secretKey = $config->getSecretKey();
        $this->checksumKey = $config->getChecksumKey();
        $this->endpoint = Environment::endpoint($config->getEnv());
        $this->http = $http ?? new HttpClient();
    }

    /**
     * Create a payment request and get the redirect URL.
     *
     * @param CreatePaymentRequest $request
     * @return ResponseInterface
     * @throws JsonException
     */
    public function createPayment(CreatePaymentRequest $request): ResponseInterface
    {
        $time = (string)time();
        $payload = array_merge([
            'merchantKey' => $this->clientId,
            'time' => $time,
        ], $request->toPayload());

        $message = MessageBuilder::instance()
            ->with($time, $this->endpoint . '/payments/create', 'POST')
            ->withParams($payload)
            ->build();

        $signature = Signature::sign($message, $this->secretKey);
        $redirectUrl = $this->endpoint . '/portal?' . http_build_query([
            'baseEncode' => base64_encode(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)),
            'signature' => $signature,
        ]);

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
     * Create a refund request.
     *
     * @param CreateRefundRequest $request
     * @return ResponseInterface
     */
    public function refund(CreateRefundRequest $request): ResponseInterface
    {
        $time = (string)time();
        $payload = $request->toPayload();

        $message = MessageBuilder::instance()
            ->with($time, $this->endpoint . '/refunds/create', 'POST')
            ->withParams($payload)
            ->build();

        $signature = Signature::sign($message, $this->secretKey);
        $headers = [
            'Date' => $time,
            'Authorization' => 'Signature Algorithm=HS256,Credential=' . $this->clientId . ',SignedHeaders=,Signature=' . $signature,
        ];

        $res = $this->http->post($this->endpoint . '/refunds/create', $payload, $headers);

        $ok = isset($res['status']) && $res['status'] >= 200 && $res['status'] < 300;

        // Response body processing depends on implementation of HttpClient::post which returns array
        $body = $res['body'] ?? [];
        return new BasicResponse($ok, is_array($body) ? $body : ['raw' => $body], (string)($body['message'] ?? ''));
    }

    /**
     * Payer authentication request.
     *
     * @param PayerAuthRequest $request
     * @return ResponseInterface
     */
    public function payerAuth(PayerAuthRequest $request): ResponseInterface
    {
        $time = (string)time();
        $payload = $request->toPayload();
        $message = MessageBuilder::instance()
            ->with($time, $this->endpoint . '/v2/payments/payer-auth', 'POST')
            ->withParams(['json' => json_encode($payload)])
            ->build();

        $signature = Signature::sign($message, $this->secretKey);
        $headers = [
            'Date' => $time,
            'Authorization' => 'Signature Algorithm=HS256,Credential=' . $this->clientId . ',SignedHeaders=,Signature=' . $signature,
        ];

        $res = $this->http->post($this->endpoint . '/v2/payments/payer-auth', $payload, $headers);

        $ok = isset($res['status']) && $res['status'] >= 200 && $res['status'] < 300;

        $body = $res['body'] ?? [];
        return new BasicResponse($ok, is_array($body) ? $body : ['raw' => $body], (string)($body['message'] ?? ''));
    }

    /**
     * Verify IPN/Return signature from 9Pay.
     *
     * @param string $result
     * @param string $checksum
     * @return bool
     */
    public function verify(string $result, string $checksum): bool
    {
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
