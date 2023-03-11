<?php

namespace Tests\Unit\Factories;

use App\Factories\JWTCookieFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

class JWTCookieFactoryTest extends TestCase
{
    public function testMakeSecured()
    {
        $token = '1234567890';
        $apiUrl = 'http://somedomain.com:1234/';
        $expectedDomain = 'somedomain.com';
        $expectedPath = '/';
        $isSecured = true;

        $actual = JWTCookieFactory::make($token, $apiUrl, $isSecured);
        $this->assertInstanceOf(Cookie::class, $actual);
        $this->assertEquals($expectedDomain, $actual->getDomain());
        $this->assertEquals($expectedPath, $actual->getPath());
        $this->assertEquals($token, $actual->getValue());
        $this->assertTrue($actual->isSecure());
        $this->assertTrue($actual->isHttpOnly());
        $this->assertNotContains(
            $actual->getSameSite(),
            [
                Cookie::SAMESITE_LAX,
                Cookie::SAMESITE_STRICT
            ]
        );
    }

    public function testMakeUnsecured()
    {
        $token = '1234567890';
        $apiUrl = 'http://somedomain.com:1234/';
        $isSecured = false;

        $actual = JWTCookieFactory::make($token, $apiUrl, $isSecured);
        $this->assertFalse($actual->isSecure());
        $this->assertNotEquals(Cookie::SAMESITE_NONE, $actual->getSameSite());
    }

    public function testMakeExpired()
    {
        $actual = JWTCookieFactory::makeExpired();
        $this->assertInstanceOf(Cookie::class, $actual);
        $this->assertNull($actual->getValue());
    }
}
