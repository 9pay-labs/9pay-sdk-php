<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Support;

use NinePay\Support\BasicResponse;
use PHPUnit\Framework\TestCase;

class BasicResponseTest extends TestCase
{
    public function testGetters(): void
    {
        $data = ['foo' => 'bar'];
        $response = new BasicResponse(true, $data, 'Success message');

        $this->assertTrue($response->isSuccess());
        $this->assertEquals($data, $response->getData());
        $this->assertEquals('Success message', $response->getMessage());
    }

    public function testDefaultValues(): void
    {
        $response = new BasicResponse(false);

        $this->assertFalse($response->isSuccess());
        $this->assertEquals([], $response->getData());
        $this->assertEquals('', $response->getMessage());
    }
}
