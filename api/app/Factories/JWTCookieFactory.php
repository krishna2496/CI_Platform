<?php

namespace App\Factories;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * @coversDefaultClass  \App\Factories\JWTCookieFactory
 */
class JWTCookieFactory
{
    public const COOKIE_NAME = 'token';

    /**
     * @param string $token The JWT token that will be stored in the cookie
     * @param string $apiUrl The API URL, from which we'll retrieve the domain
     * @param bool $isSecured Whether the cookie should be secured or not (not enabled by default to support local env)
     * @return Cookie
     */
    public static function make(string $token, string $apiUrl, bool $isSecured) : Cookie
    {
        /*
         * On local dev, cookies will not be secured and should
         * not have the "SameSite" attribute set to "none"
         * otherwise they are not created.
         */
        $sameSite = Cookie::SAMESITE_LAX;

        /*
         * Secured cookies are expected to be used on staging and production.
         * Since CIP frontend and API are hosted on different domains in production,
         * we need to set the "SameSite" attribute to "none", otherwise there will
         * be a security issue and the cookie will not be created.
         */
        if ($isSecured) {
            $sameSite = Cookie::SAMESITE_NONE;
        }

        return new Cookie(
            self::COOKIE_NAME,
            $token,
            strtotime('+4hours'),
            '/',
            parse_url($apiUrl, PHP_URL_HOST),
            $isSecured,
            true,
            false,
            $sameSite
        );
    }

    /**
     * In order to remove the cookie from the client's browser when logging out.
     *
     * @return Cookie
     */
    public static function makeExpired() : Cookie
    {
        return new Cookie(self::COOKIE_NAME, null);
    }
}
