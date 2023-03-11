<?php

namespace App\Helpers;

class IPValidationHelper
{
    /**
     * Max possible IP address
     */
    const MAX_IP = '255.255.255.255';

    /**
     * Min CIDR number
     */
    const MIN_CIDR = 0;

    /**
     * Max CIDR number
     */
    const MAX_CIDR = 32;

    /**
     * Verify given IP with given whitelist IPs 
     *
     * @param string $ip IP Address that we need to check
     * @param array $whitelists all whitelisted IP with pattern, wildcard or CIDR 
     *
     * @return $this
     */
    public function verify($ip, array $whitelists)
    {
        if (!$this->validIp($ip)) {
            return false;
        }

        $direct = $this->byDirect($ip, $whitelists);

        if ($direct) {
            return true;
        }

        foreach ($whitelists as $whitelist) {
            $wildcard = $this->byWildcard($ip, $whitelist);
            if ($wildcard) {
                return true;
            }
            $range = $this->byRange($ip, $whitelist);
            if ($range) {
                return true;
            }
            $cidr = $this->byCidr($ip, $whitelist);
            if ($cidr) {
                return true;
            }
        }

        return false;
    }

    /**
     * Direct IP comparison checking
     * Usage:
     *     $this->byDirect('127.0.0.1',[
     *        '127.0.0.1',
     *        '127.0.0.3',
     *        '127.0.0.3',
     *     ]);
     *
     * @param string $ip IP Address that we need to check
     * @param array $whitelists all whitelisted IPs
     *
     * @return bool
     */
    public function byDirect($ip, $whitelists = [])
    {
        if (!$this->validIp($ip)) {
            return false;
        }

        return in_array($ip, $whitelists);
    }

    /**
     * Wildcard IP comparison checking
     * Usage:
     *     $this->byWildcard("127.0.0.1","192.0.*.*");
     *
     * @param string $ip IP Address that we need to check
     * @param string $pattern specific pattern with wildcard
     *
     * @return bool
     */
    public function byWildcard($ip, $pattern)
    {
        if (!strstr($pattern, '*') || !$this->validIp($ip)) {
            return false;
        }

        $min = str_replace('*', '0', $pattern);
        $max = str_replace('*', '255', $pattern);
        $range = "$min-$max";

        return $this->byRange($ip, $range);
    }

    /**
     * Range IP comparison checking
     * Usage:
     *     $this->byRange("127.0.0.1","192.168.0.1-192.168.0.63");
     *
     * @param string $ip IP Address that we need to check
     * @param string $pattern specific pattern for range
     *
     * @return bool
     */
    public function byRange($ip, $pattern)
    {
        if (!strstr($pattern, '-') || !$this->validIp($ip)) {
            return false;
        }

        list($min, $max) = explode('-', $pattern, 2);
        $min = ip2long($min);
        $max = ip2long($max);
        $ip = ip2long($ip);

        return ($ip >= $min && $ip <= $max);
    }

    /**
     * CIDR IP comparison checking
     * Usage:
     *     $this->byCidr("127.0.0.1","192.168.0.1/25 || 192.168.0.1/255.255.255.128");
     *
     * @param string $ip IP Address that we need to check
     * @param string $pattern specific pattern for CIDR
     *
     * @return bool
     */
    public function byCidr($ip, $pattern)
    {
        if (!strstr($pattern, '/') || !$this->validIp($ip)) {
            return false;
        }

        list($range, $netmask) = explode('/', $pattern, 2);

        if (!strstr($netmask, '.')) {
            $netmask = $this->cidrToMask((int) $netmask);
            if ($netmask === false) {
                return false;
            }
        }

        $cidr = $this->alignedCidr($range, $netmask);
        $cidr = explode('/',$cidr);
        $ip = ip2long($ip);
        $ip1 = ip2long($cidr[0]);
        $ip2 = ($ip1 + pow(2, (32 - (int) $cidr[1])) - 1);

        return ($ip1 <= $ip && $ip <= $ip2);
    }

    /**
     * Aligned CIDR
     *
     * @param string $ip 
     * @param string $netmast 
     *
     * @return string
     */
    public function alignedCidr($ip, $netmask)
    {
        $cidr = $this->maskToCidr($netmask);
        if ($cidr === false) {
            return false;
        }
        $alignedIP = long2ip(ip2long($ip) & ip2long($netmask));
        return "$alignedIP/$cidr";
    }

    /**
     * CIDR to netmask conversion
     *
     * @param int $cidr CIDR 0 - 32
     *
     * @return string
     */
    public function cidrToMask($cidr)
    {
        if (!$this->validCidr($cidr)) {
            return false;
        }
        return long2ip(-1 << (32 - (int) $cidr));
    }

    /**
     * netmask to CIDR conversion
     *
     * @param int $netmask
     *
     * @return int|bool
     */
    public function maskToCidr($netmask)
    {
        if (!$this->validMask($netmask)) {
            return false;
        }
        $long = ip2long($netmask);
        $base = ip2long(self::MAX_IP);

        return round(32 - log(($long ^ $base) + 1, 2), 0);
    }

    /**
     * Check if netmask given is valid
     *
     * @param string $mask
     *
     * @return bool
     */
    public function validMask($mask)
    {
        $netmask = ip2long($mask);
        if (!$netmask) {
            return false;
        }
        $neg = ((~ (int) $netmask) & 0xFFFFFFFF);

        return (($neg + 1) & $neg) === 0;
    }

    /**
     * Check if given IP is valid
     *
     * @param string $ip
     *
     * @return bool
     */
    public function validIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * Check if given CIRD is valid
     *
     * @param string $cidr ( 0-32 | Netmask )
     *
     * @return bool
     */
    public function validCidr($cidr)
    {
        if (strstr($cidr, '.')) {
            return $this->validMask($cidr);
        }
        
        return (is_numeric($cidr) && $cidr >= self::MIN_CIDR && $cidr <= self::MAX_CIDR);
    }

    /**
     * Check if given Pattern is valid CIRD format
     * Usage:
     *     $this->validCidr("92.168.0.1/25", "192.168.0.1/255.255.255.128");
     *
     * @param string $pattern
     *
     * @return bool
     */
    public function validCidrPattern($pattern)
    {
        $pattern = explode('/', $pattern);
        if (count($pattern) !== 2) {
            return false;
        }

        list ($ip, $cidr) = $pattern;
        return $this->validIp($ip) && $this->validCidr($cidr);
    }

    /**
     * Check if given Pattern is valid range format
     * Usage:
     *     $this->validCidr("92.168.0.1-25", "192.168.0.1-192.168.0.25");
     *
     * @param string $pattern
     *
     * @return bool
     */
    public function validRangePattern($pattern)
    {
        $pattern = explode('-', $pattern);
        if (count($pattern) !== 2) {
            return false;
        }

        list ($min, $max) = $pattern;
        if (!strstr($max, '.')) {
            $base = explode('.', $min);
            array_pop($base);
            array_push($base, $max);
            $max = implode('.', $base);
        }

        return $this->validIp($min) && $this->validIp($max) && (ip2long($max) > ip2long($min));
    }

    /**
     * Check if given Pattern is valid wildcard format
     * Usage:
     *     $this->validCidr("92.168.0.*", "92.168.*.*");
     *
     * @param string $pattern
     *
     * @return bool
     */
    public function validWildcardPattern($pattern)
    {
        $octets = explode('.', $pattern);
        if (!strstr($pattern, '*') || count($octets) !== 4) {
            return false;
        }

        $pattern = str_replace('*', '0', $pattern);
        return $this->validIp($pattern) ? true : false;
    }

}
