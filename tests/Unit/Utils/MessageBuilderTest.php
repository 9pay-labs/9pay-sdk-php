<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Utils;

use NinePay\Utils\MessageBuilder;
use NinePay\Exceptions\PaymentException;
use PHPUnit\Framework\TestCase;

class MessageBuilderTest extends TestCase
{
    public function testBuildThrowsExceptionWhenMissingUriOrDate(): void
    {
        $this->expectException(PaymentException::class);
        MessageBuilder::instance()->build();
    }

    public function testBuildGetMessage(): void
    {
        $date = '2023-10-27';
        $uri = 'https://api.9pay.vn/test';
        $message = MessageBuilder::instance()
            ->with($date, $uri, 'GET')
            ->build();

        $expected = "GET\n{$uri}\n{$date}";
        $this->assertEquals($expected, $message);
    }

    public function testBuildGetMessageWithParams(): void
    {
        $date = '2023-10-27';
        $uri = 'https://api.9pay.vn/test';
        $params = [
            'b' => '2',
            'a' => '1',
        ];
        $message = MessageBuilder::instance()
            ->with($date, $uri, 'GET')
            ->withParams($params)
            ->build();

        // Params should be sorted by key: a=1&b=2
        $expected = "GET\n{$uri}\n{$date}\na=1&b=2";
        $this->assertEquals($expected, $message);
    }

    public function testBuildPostMessageWithBody(): void
    {
        $date = '2023-10-27';
        $uri = 'https://api.9pay.vn/test';
        $body = ['foo' => 'bar'];
        $message = MessageBuilder::instance()
            ->with($date, $uri, 'POST')
            ->withBody($body)
            ->build();

        $jsonBody = json_encode($body);
        $hashedBody = base64_encode(hash('sha256', $jsonBody, true));
        
        $expected = "POST\n{$uri}\n{$date}\n{$hashedBody}";
        $this->assertEquals($expected, $message);
    }

    public function testToString(): void
    {
        $date = '2023-10-27';
        $uri = 'https://api.9pay.vn/test';
        $builder = MessageBuilder::instance()->with($date, $uri, 'GET');
        
        $this->assertEquals($builder->build(), (string)$builder);
    }

    public function testToStringReturnsEmptyOnFailure(): void
    {
        $builder = MessageBuilder::instance();
        $this->assertEquals('', (string)$builder);
    }
}
