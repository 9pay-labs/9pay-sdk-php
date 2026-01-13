<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Utils;

use NinePay\Utils\Environment;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    public function testEndpointReturnsCorrectUrls(): void
    {
        $this->assertEquals(Environment::SAND, Environment::endpoint('SANDBOX'));
        $this->assertEquals(Environment::PROD, Environment::endpoint('PRODUCTION'));
    }

    public function testEndpointIsCaseInsensitive(): void
    {
        $this->assertEquals(Environment::SAND, Environment::endpoint('sandbox'));
        $this->assertEquals(Environment::PROD, Environment::endpoint('production'));
    }

    public function testEndpointDefaultsToSandbox(): void
    {
        $this->assertEquals(Environment::SAND, Environment::endpoint('invalid'));
        $this->assertEquals(Environment::SAND, Environment::endpoint(''));
    }
}
