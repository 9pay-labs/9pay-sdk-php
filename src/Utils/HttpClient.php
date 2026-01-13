<?php
declare(strict_types=1);

namespace NinePay\Utils;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class HttpClient
 * 
 * Wrapper for GuzzleHttp to send API requests.
 */
class HttpClient
{
    /** @var GuzzleClient */
    private GuzzleClient $client;

    /**
     * HttpClient constructor.
     *
     * @param GuzzleClient|null $client
     */
    public function __construct(?GuzzleClient $client = null)
    {
        $this->client = $client ?? new GuzzleClient([
            'http_errors' => false,
            'timeout' => 15,
        ]);
    }

    /**
     * Send a GET request.
     *
     * @param string $url
     * @param array $headers
     * @return array{status:int,body:mixed,headers:array}
     */
    public function get(string $url, array $headers = []): array
    {
        try {
            $res = $this->client->request('GET', $url, [
                'headers' => $headers,
            ]);
        } catch (GuzzleException $e) {
            return ['status' => 0, 'body' => ['error' => $e->getMessage()], 'headers' => []];
        }

        return $this->normalizeResponse($res->getStatusCode(), (string)$res->getBody(), $res->getHeaders());
    }

    /**
     * Send a POST request.
     *
     * @param string $url
     * @param array|string|null $jsonOrBody
     * @param array $headers
     * @return array{status:int,body:mixed,headers:array}
     */
    public function post(string $url, $jsonOrBody = null, array $headers = []): array
    {
        $options = ['headers' => $headers];
        if (is_array($jsonOrBody)) {
            $options['json'] = $jsonOrBody;
        } elseif (is_string($jsonOrBody)) {
            $options['body'] = $jsonOrBody;
        }
        try {
            $res = $this->client->request('POST', $url, $options);
        } catch (GuzzleException $e) {
            return ['status' => 0, 'body' => ['error' => $e->getMessage()], 'headers' => []];
        }
        return $this->normalizeResponse($res->getStatusCode(), (string)$res->getBody(), $res->getHeaders());
    }

    /**
     * @param array $rawHeaders
     * @return array{status:int,body:mixed,headers:array}
     */
    private function normalizeResponse(int $status, string $body, array $rawHeaders): array
    {
        $data = json_decode($body, true);
        $parsed = is_array($data) ? $data : $body;
        return [
            'status' => $status,
            'body' => $parsed,
            'headers' => $rawHeaders,
        ];
    }
}
