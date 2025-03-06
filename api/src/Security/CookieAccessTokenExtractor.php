<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

class CookieAccessTokenExtractor implements AccessTokenExtractorInterface
{
    public const AUTH_COOKIE_NAME = 'api_token';

    public function extractAccessToken(Request $request): ?string
    {
        return $request->cookies->get(self::AUTH_COOKIE_NAME);
    }
}
