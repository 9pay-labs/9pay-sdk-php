<?php
declare(strict_types=1);

namespace NinePay\Gateways;

use JsonException;
use NinePay\Config\NinePayConfig;
use NinePay\Contracts\PaymentGatewayInterface;
use NinePay\Contracts\ResponseInterface;
use NinePay\Gateways\Support\NinePayApiSupportTrait;
use NinePay\Request\AuthorizeCardPaymentRequest;
use NinePay\Request\CapturePaymentRequest;
use NinePay\Request\CreatePaymentRequest;
use NinePay\Request\CreateRefundRequest;
use NinePay\Request\PayerAuthRequest;
use NinePay\Request\ReverseCardPaymentRequest;
use NinePay\Support\BasicResponse;
use NinePay\Utils\Environment;
use NinePay\Utils\HttpClient;
use NinePay\Utils\MessageBuilder;
use NinePay\Utils\Signature;
use NinePay\Utils\UnicodeFormat;

class NinePayGateway implements PaymentGatewayInterface
{
    use NinePayApiSupportTrait;

    private string $clientId;
    private string $secretKey;
    private string $checksumKey;
    private string $endpoint;
    private HttpClient $http;

    public function __construct(NinePayConfig $config, ?HttpClient $http = null)
    {
        $this->clientId = $config->getMerchantId();
        $this->secretKey = $config->getSecretKey();
        $this->checksumKey = $config->getChecksumKey();
        $this->endpoint = Environment::endpoint($config->getEnv());
        $this->http = $http ?? new HttpClient();
    }

    /**
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

    public function inquiry(string $transactionId): ResponseInterface
    {
        $time = (string)time();
        $url = $this->endpoint . '/v2/payments/' . $transactionId . '/inquire';
        $message = MessageBuilder::instance()
            ->with($time, $url, 'GET')
            ->withParams([])
            ->build();

        $signature = Signature::sign($message, $this->secretKey);
        $headers = $this->buildHeaders($time, $signature);

        $res = $this->http->get($url, $headers);
        return $this->processResponse($res);
    }

    public function refund(CreateRefundRequest $request): ResponseInterface
    {
        $time = (string)time();
        $payload = $request->toPayload();
        $url = $this->endpoint . '/refunds/create';

        $message = MessageBuilder::instance()
            ->with($time, $url, 'POST')
            ->withParams($payload)
            ->build();

        $signature = Signature::sign($message, $this->secretKey);
        $headers = $this->buildHeaders($time, $signature);
        $res = $this->http->post($this->endpoint . '/refunds/create', $payload, $headers);

        return $this->processResponse($res);
    }

    public function payerAuth(PayerAuthRequest $request): ResponseInterface
    {
        return $this->sendRequest('/v2/payments/payer-auth', $request->toPayload());
    }

    public function authorizeCardPayment(AuthorizeCardPaymentRequest $request): ResponseInterface
    {
        return $this->sendRequest('/v2/payments/authorize', $request->toPayload());
    }

    public function reverseCardPayment(ReverseCardPaymentRequest $request): ResponseInterface
    {
        return $this->sendRequest('/v2/payments/reverse-auth', $request->toPayload());
    }

    public function capture(CapturePaymentRequest $request): ResponseInterface
    {
        return $this->sendRequest('/v2/payments/capture', $request->toPayload());
    }


    public function verify(string $result, string $checksum): bool
    {
        if ($result === '' || $checksum === '') {
            return false;
        }
        $hashChecksum = strtoupper(hash('sha256', $result . $this->checksumKey));
        return hash_equals($hashChecksum, $checksum);
    }

    public function decodeResult(string $result): string
    {
        return UnicodeFormat::urlsafeB64Decode($result);
    }
}
