<?php

namespace Tests\Unit\Helpers;

use App\Helpers\IPValidationHelper;
use TestCase;

class IPValidationHelperTest extends TestCase
{
    /**
     * @var IPValidationHelper
     */
    private $ipValidator;

    /**
     * Start up the class and data neede for the class to tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->ipValidator = new IPValidationHelper();
    }

    /**
     * @testdox Test verify method on IPValidationHelper Class
     */
    public function testVerify()
    {
        $whitelistedIps = [
            '192.168.0.1',
            '192.168.1.*',
            '192.169.*.*',
            '192.168.2.0-192.168.2.10',
            '192.168.3.0/26',
            '192.168.4.0/255.255.255.192'
        ];

        $testingIps = [
            '192.168.0.1' => true,
            '192.168.1.99' => true,
            '192.169.99.99' => true,
            '192.168.2.10' => true,
            '192.168.4.36' => true,
            '192.168.0.9' => false,
            '192.168.1.9999' => false,
            '192.170.0.0' => false,
            '192.168.2.70' => false,
            '192.168.4.90' => false
        ];

        foreach ($testingIps as $ip => $expected) {
            $result = $this->ipValidator->verify($ip, $whitelistedIps);
            $this->assertSame($result, $expected);
        }
    }

    /**
     * @testdox Test byDirect method on IPValidationHelper Class
     */
    public function testByDirect()
    {
        $whitelistedIps = [
            '192.168.0.1',
            '192.168.1.1',
            '192.168.2.1',
            '192.168.3.1',
            '192.168.4.1',
            '192.168.5.1',
            '192.168.6.1'
        ];

        $testingIps = [
            '192.168.1.1' => true,
            '192.168.2.2' => false,
            '192.168.3.1' => true,
            '192.168.4.4' => false,
            '192.168.5.1' => true
        ];

        foreach ($testingIps as $ip => $expected) {
            $result = $this->ipValidator->byDirect($ip, $whitelistedIps);
            $this->assertSame($result, $expected);
        }
    }

    /**
     * @testdox Test byWildcard method on IPValidationHelper Class
     */
    public function testByWildcard()
    {
        $testData = [
            '192.168.1.*' => [
                true => [
                    '192.168.1.0',
                    '192.168.1.1',
                    '192.168.1.2',
                    '192.168.1.3',
                    '192.168.1.4',
                    '192.168.1.5',
                    '192.168.1.6'
                ],
                false => [
                    '192.168.2.0',
                    '192.168.3.1',
                    '192.168.4.2',
                    '192.168.5.3',
                    '192.168.6.4',
                    '192.168.7.5',
                    '192.168.8.6'
                ]
            ],
            '192.168.*.*' => [
                true => [
                    '192.168.2.0',
                    '192.168.3.1',
                    '192.168.4.2',
                    '192.168.5.3',
                    '192.168.6.4',
                    '192.168.7.5',
                    '192.168.8.6'
                ],
                false => [
                    '192.169.2.0',
                    '192.169.3.1',
                    '192.169.4.2',
                    '192.169.5.3',
                    '192.169.6.4',
                    '192.169.7.5',
                    '192.169.8.6'
                ]
            ]
        ];

        $this->assertData($testData, 'byWildcard');
    }

    /**
     * @testdox Test byRange method on IPValidationHelper Class
     */
    public function testByRange()
    {
        $testData = [
            '192.168.1.0-192.168.1.6' => [
                true => [
                    '192.168.1.0',
                    '192.168.1.1',
                    '192.168.1.2',
                    '192.168.1.3',
                    '192.168.1.4',
                    '192.168.1.5',
                    '192.168.1.6'
                ],
                false => [
                    '192.168.1.7',
                    '192.168.2.8',
                    '192.168.1.9',
                    '192.168.3.10',
                    '192.168.1.11',
                    '192.168.4.12',
                    '192.168.1.13'
                ]
            ],
            '192.168.2.6-192.168.2.12' => [
                true => [
                    '192.168.2.6',
                    '192.168.2.7',
                    '192.168.2.8',
                    '192.168.2.9',
                    '192.168.2.10',
                    '192.168.2.11',
                    '192.168.2.12'
                ],
                false => [
                    '192.168.2.13',
                    '192.168.1.14',
                    '192.168.2.15',
                    '192.168.3.16',
                    '192.168.2.17',
                    '192.168.4.18',
                    '192.168.2.19'
                ]
            ],
        ];

        $this->assertData($testData, 'byRange');
    }

    private function assertData($payload, $method)
    {
        foreach ($payload as $pattern => $data) {
            foreach ($data as $expected => $ips) {
                foreach ($ips as $ip) {
                    $result = $this->ipValidator->$method($ip, $pattern);
                    $this->assertSame($result, (bool) $expected);
                }
            }
        }
    }

    /**
     * @testdox Test byCidr method on IPValidationHelper Class
     */
    public function testByCidr()
    {
        $testData = [
            // All CIDR /Slash - Expected range
            '192.168.0.0/1' => '128.0.0.0-255.255.255.255',
            '192.168.0.0/2' => '192.0.0.0-255.255.255.255',
            '192.168.0.0/3' => '192.0.0.0-223.255.255.255',
            '192.168.0.0/4' => '192.0.0.0-207.255.255.255',
            '192.168.0.0/5' => '192.0.0.0-199.255.255.255',
            '192.168.0.0/6' => '192.0.0.0-195.255.255.255',
            '192.168.0.0/7' => '192.0.0.0-193.255.255.255',
            '192.168.0.0/8' => '192.0.0.0-192.255.255.255',
            '192.168.0.0/8' => '192.128.0.0-192.255.255.255',
            '192.168.0.0/10' => '192.128.0.0-192.191.255.255',
            '192.168.0.0/11' => '192.160.0.0-192.191.255.255',
            '192.168.0.0/12' => '192.160.0.0-192.175.255.255',
            '192.168.0.0/13' => '192.168.0.0-192.175.255.255',
            '192.168.0.0/14' => '192.168.0.0-192.171.255.255',
            '192.168.0.0/15' => '192.168.0.0-192.169.255.255',
            '192.168.0.0/16' => '192.168.0.0-192.168.255.255',
            '192.168.0.0/17' => '192.168.0.0-192.168.127.255',
            '192.168.0.0/18' => '192.168.0.0-192.168.63.255',
            '192.168.0.0/19' => '192.168.0.0-192.168.31.255',
            '192.168.0.0/20' => '192.168.0.0-192.168.15.255',
            '192.168.0.0/21' => '192.168.0.0-192.168.7.255',
            '192.168.0.0/22' => '192.168.0.0-192.168.3.255',
            '192.168.0.0/23' => '192.168.0.0-192.168.1.255',
            '192.168.0.0/24' => '192.168.0.0-192.168.0.255',
            '192.168.0.0/25' => '192.168.0.0-192.168.0.127',
            '192.168.0.0/26' => '192.168.0.0-192.168.0.63',
            '192.168.0.0/27' => '192.168.0.0-192.168.0.31',
            '192.168.0.0/28' => '192.168.0.0-192.168.0.15',
            '192.168.0.0/29' => '192.168.0.0-192.168.0.7',
            '192.168.0.0/30' => '192.168.0.0-192.168.0.3',
            '192.168.0.0/31' => '192.168.0.0-192.168.0.1',
            '192.168.0.0/32' => '192.168.0.0-192.168.0.0',
            // All CIDR Netmask - Expected range
            '192.168.0.0/128.0.0.0' => '128.0.0.0-255.255.255.255',
            '192.168.0.0/192.0.0.0' => '192.0.0.0-255.255.255.255',
            '192.168.0.0/224.0.0.0' => '192.0.0.0-223.255.255.255',
            '192.168.0.0/240.0.0.0' => '192.0.0.0-207.255.255.255',
            '192.168.0.0/248.0.0.0' => '192.0.0.0-199.255.255.255',
            '192.168.0.0/252.0.0.0' => '192.0.0.0-195.255.255.255',
            '192.168.0.0/254.0.0.0' => '192.0.0.0-193.255.255.255',
            '192.168.0.0/255.0.0.0' => '192.0.0.0-192.255.255.255',
            '192.168.0.0/255.128.0.0' => '192.128.0.0-192.255.255.255',
            '192.168.0.0/255.192.0.0' => '192.128.0.0-192.191.255.255',
            '192.168.0.0/255.224.0.0' => '192.160.0.0-192.191.255.255',
            '192.168.0.0/255.240.0.0' => '192.160.0.0-192.175.255.255',
            '192.168.0.0/255.248.0.0' => '192.168.0.0-192.175.255.255',
            '192.168.0.0/255.252.0.0' => '192.168.0.0-192.171.255.255',
            '192.168.0.0/255.254.0.0' => '192.168.0.0-192.169.255.255',
            '192.168.0.0/255.255.0.0' => '192.168.0.0-192.168.255.255',
            '192.168.0.0/255.255.128.0' => '192.168.0.0-192.168.127.255',
            '192.168.0.0/255.255.192.0' => '192.168.0.0-192.168.63.255',
            '192.168.0.0/255.255.224.0' => '192.168.0.0-192.168.31.255',
            '192.168.0.0/255.255.240.0' => '192.168.0.0-192.168.15.255',
            '192.168.0.0/255.255.248.0' => '192.168.0.0-192.168.7.255',
            '192.168.0.0/255.255.252.0' => '192.168.0.0-192.168.3.255',
            '192.168.0.0/255.255.254.0' => '192.168.0.0-192.168.1.255',
            '192.168.0.0/255.255.255.0' => '192.168.0.0-192.168.0.255',
            '192.168.0.0/255.255.255.128' => '192.168.0.0-192.168.0.127',
            '192.168.0.0/255.255.255.192' => '192.168.0.0-192.168.0.63',
            '192.168.0.0/255.255.255.224' => '192.168.0.0-192.168.0.31',
            '192.168.0.0/255.255.255.240' => '192.168.0.0-192.168.0.15',
            '192.168.0.0/255.255.255.248' => '192.168.0.0-192.168.0.7',
            '192.168.0.0/255.255.255.252' => '192.168.0.0-192.168.0.3',
            '192.168.0.0/255.255.255.254' => '192.168.0.0-192.168.0.1',
            '192.168.0.0/255.255.255.255' => '192.168.0.0-192.168.0.0',
        ];

        foreach ($testData as $cidr => $expectedIpRange) {
            // Will test 192.168.0-255.0-255 IPS with expected CIDR range
            for ($max = 0; $max < 255; $max++) {
                $ip = "192.168.$max.$max";
                $this->assertSame(
                    $this->ipValidator->byRange($ip, $expectedIpRange),
                    $this->ipValidator->byCidr($ip, $cidr)
                );
            }
        }

    }

    /**
     * @testdox Test cidrToMask method on IPValidationHelper Class
     */
    public function testCidrToMask()
    {
        $testData = $this->netmaskCidr();

        foreach ($testData as $slash => $netmask) {
            $result = $this->ipValidator->cidrToMask($slash);
            $this->assertSame($result, $netmask);
        }
    }

    /**
     * @testdox Test maskToCidr method on IPValidationHelper Class
     */
    public function testMaskToCidr()
    {
        $testData = $this->netmaskCidr();

        foreach ($testData as $slash => $netmask) {
            $result = $this->ipValidator->maskToCidr($netmask);
            $this->assertSame((int) $result, $slash);
        }
    }

    /**
     * @testdox Test validMask method on IPValidationHelper Class
     */
    public function testValidMask()
    {
        $testData = [
            '255.255.255.255' => true,
            '255.255.255.254' => true,
            '255.255.255.252' => true,
            '255.255.255.248' => true,
            '255.255.255.240' => true,
            '255.255.255.224' => true,
            '255.255.255.192' => true,
            '255.255.255.128' => true,
            '255.255.255.0' => true,
            '255.255.254.0' => true,
            '255.255.252.0' => true,
            '255.255.248.0' => true,
            '255.255.240.0' => true,
            '255.255.224.0' => true,
            '255.255.192.0' => true,
            '255.255.128.0' => true,
            '255.255.0.0' => true,
            '255.254.0.0' => true,
            '255.252.0.0' => true,
            '255.248.0.0' => true,
            '255.240.0.0' => true,
            '255.224.0.0' => true,
            '255.192.0.0' => true,
            '255.128.0.0' => true,
            '255.0.0.0' => true,
            '254.0.0.0' => true,
            '252.0.0.0' => true,
            '248.0.0.0' => true,
            '240.0.0.0' => true,
            '224.0.0.0' => true,
            '192.0.0.0' => true,
            '128.0.0.0' => true,
            '255.1.2.3' => false,
            '254.1.2.3' => false,
            '252.1.2.3' => false,
            '248.1.2.3' => false,
            '240.1.2.3' => false,
            '224.1.2.3' => false,
            '192.1.2.3' => false,
            '128.1.2.3' => false,
            '0.1.2.3' => false
        ];

        foreach ($testData as $netmask => $expected) {
            $result = $this->ipValidator->validMask($netmask);
            $this->assertSame($result, $expected);
        }
    }

    /**
     * @testdox Test validIp method on IPValidationHelper Class
     */
    public function testValidIp()
    {
        $testData = [
            '255.255.255.255' => true,
            '255.255.255.254' => true,
            '255.255.255.252' => true,
            '255.255.255.248' => true,
            '255.255.255.240' => true,
            '255.255.255.224' => true,
            '255.255.255.192' => true,
            '255.255.255.128' => true,
            '99999.255.255.255' => false,
            '99999.255.255.254' => false,
            '99999.255.255.252' => false,
            '99999.255.255.248' => false,
            '99999.255.255.240' => false,
            '99999.255.255.224' => false,
            '99999.255.255.192' => false,
            '99999.255.255.128' => false
        ];

        foreach ($testData as $ip => $expected) {
            $result = $this->ipValidator->validIp($ip);
            $this->assertSame($result ? true : false, $expected);
        }
    }

    /**
     * @testdox Test validCidrPattern method on IPValidationHelper Class
     */
    public function testValidCidrPattern()
    {
        $testData = [
            '192.160.0.1/255.255.255.255' => true,
            '255.255.255.254/16' => true,
            '255.255.255.252/12312312313' => false,
            '255.255.255.252/12.12.12.999' => false
        ];

        foreach ($testData as $pattern => $expected) {
            $result = $this->ipValidator->validCidrPattern($pattern);
            $this->assertSame($result, $expected);
        }
    }

    /**
     * @testdox Test validRangePattern method on IPValidationHelper Class
     */
    public function testValidRangePattern()
    {
        $testData = [
            '192.160.0.1-30' => true,
            '192.160.0.1-192.160.0.30' => true,
            '192.160.0.50-30' => false,
            '192.160.0.50-192.160.0.30' => false
        ];

        foreach ($testData as $pattern => $expected) {
            $result = $this->ipValidator->validRangePattern($pattern);
            $this->assertSame($result, $expected);
        }
    }

    /**
     * @testdox Test validWildcardPattern method on IPValidationHelper Class
     */
    public function testValidWildcardPattern()
    {
        $testData = [
            '192.160.0.*' => true,
            '192.160.*.*' => true,
            '192.160.0.50-30' => false,
            '192.160.0.50-192.160.0.30' => false
        ];

        foreach ($testData as $pattern => $expected) {
            $result = $this->ipValidator->validWildcardPattern($pattern);
            $this->assertSame($result, $expected);
        }
    }

    private function netmaskCidr()
    {
        return  [
            '32' => '255.255.255.255',
            '31' => '255.255.255.254',
            '30' => '255.255.255.252',
            '29' => '255.255.255.248',
            '28' => '255.255.255.240',
            '27' => '255.255.255.224',
            '26' => '255.255.255.192',
            '25' => '255.255.255.128',
            '24' => '255.255.255.0',
            '23' => '255.255.254.0',
            '22' => '255.255.252.0',
            '21' => '255.255.248.0',
            '20' => '255.255.240.0',
            '19' => '255.255.224.0',
            '18' => '255.255.192.0',
            '17' => '255.255.128.0',
            '16' => '255.255.0.0',
            '15' => '255.254.0.0',
            '14' => '255.252.0.0',
            '13' => '255.248.0.0',
            '12' => '255.240.0.0',
            '11' => '255.224.0.0',
            '10' => '255.192.0.0',
            '9' => '255.128.0.0',
            '8' => '255.0.0.0',
            '7' => '254.0.0.0',
            '6' => '252.0.0.0',
            '5' => '248.0.0.0',
            '4' => '240.0.0.0',
            '3' => '224.0.0.0',
            '2' => '192.0.0.0',
            '1' => '128.0.0.0',
            '0' => '0.0.0.0'
        ];
    }

}