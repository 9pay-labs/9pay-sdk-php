<?php

namespace NinePay\Gateways\Support;

use NinePay\Contracts\ResponseInterface;
use NinePay\Support\BasicResponse;
use NinePay\Utils\MessageBuilder;
use NinePay\Utils\Signature;

trait NinePayApiSupportTrait
{
    private function buildHeaders(string $time, string $signature): array
    {
        return [
            'Date' => $time,
            'Authorization' => sprintf(
                'Signature Algorithm=HS256,Credential=%s,SignedHeaders=,Signature=%s',
                $this->clientId,
                $signature
            ),
        ];
    }

    private function processResponse(array $res): BasicResponse
    {
        $statusOk = isset($res['status']) && $res['status'] >= 200 && $res['status'] < 300;
        $body = $res['body'] ?? [];
        $data = is_array($body) ? $body : ['raw' => $body];
        $message = (string)($body['message'] ?? '');

        return new BasicResponse($statusOk, $data, $message);
    }

    private function sendRequest(string $path, array $payload, string $method = 'POST'): ResponseInterface
    {
        $time = (string)time();
        $url = $this->endpoint . $path;
        $message = MessageBuilder::instance()
            ->with($time, $url, $method)
            ->withParams(['json' => json_encode($payload)])
            ->build();

        $signature = Signature::sign($message, $this->secretKey);
        $headers = $this->buildHeaders($time, $signature);
        $res = $this->http->post($url, $payload, $headers);

        return $this->processResponse($res);
    }
}
