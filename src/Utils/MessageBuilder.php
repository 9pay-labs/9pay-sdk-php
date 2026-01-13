<?php
declare(strict_types=1);

namespace NinePay\Utils;

use NinePay\Exceptions\PaymentException;

/**
 * Class MessageBuilder
 * 
 * Supports building the Message String to create a Signature as required by 9Pay.
 */
class MessageBuilder
{
    /** @var string HTTP method (GET, POST, ...) */
    private string $method = 'GET';
    /** @var string API URI */
    private string $uri = '';
    /** @var array Headers list */
    private array $headers = [];
    /** @var string Request time (timestamp or Date string) */
    private string $date = '';
    /** @var array Query parameters or form data */
    private array $params = [];
    /** @var string|null Request body */
    private ?string $body = null;

    /**
     * Set basic information for the message.
     *
     * @param string $date
     * @param string $uri
     * @param string $method
     * @param array $headers
     * @return $this
     */
    public function with(string $date, string $uri, string $method = 'GET', array $headers = []): self
    {
        $this->date = $date;
        $this->uri = $uri;
        $this->method = $method;
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set request body.
     *
     * @param mixed $body
     * @return $this
     */
    public function withBody($body): self
    {
        if (!is_string($body)) {
            $body = json_encode($body);
        }
        $this->body = $body;
        return $this;
    }

    /**
     * Set request parameters.
     *
     * @param array $params
     * @return $this
     */
    public function withParams(array $params = []): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Build the final message string.
     *
     * @return string
     * @throws PaymentException
     */
    public function build(): string
    {
        $this->validate();

        $canonicalHeaders = $this->canonicalHeaders();
        if ($this->method === 'POST' && $this->body !== null) {
            $canonicalPayload = $this->canonicalBody();
        } else {
            $canonicalPayload = $this->canonicalParams();
        }

        $components = [$this->method, $this->uri, $this->date];
        if ($canonicalHeaders !== '') {
            $components[] = $canonicalHeaders;
        }
        if ($canonicalPayload !== '') {
            $components[] = $canonicalPayload;
        }
        return implode("\n", $components);
    }

    /**
     * Create a new instance of MessageBuilder.
     *
     * @return self
     */
    public static function instance(): self
    {
        return new self();
    }

    /**
     * Convert the object to a message string.
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->build();
        } catch (\Throwable $e) {
            return '';
        }
    }

    protected function validate(): void
    {
        if ($this->uri === '' || $this->date === '') {
            throw new PaymentException('MessageBuilder: missing uri/date');
        }
    }

    protected function canonicalHeaders(): string
    {
        if (!empty($this->headers)) {
            ksort($this->headers);
            return http_build_query($this->headers);
        }
        return '';
    }

    protected function canonicalParams(): string
    {
        if (!empty($this->params)) {
            ksort($this->params);
            return http_build_query($this->params);
        }
        return '';
    }

    protected function canonicalBody(): string
    {
        if ($this->body !== null && $this->body !== '') {
            return base64_encode(hash('sha256', $this->body, true));
        }
        return '';
    }
}
