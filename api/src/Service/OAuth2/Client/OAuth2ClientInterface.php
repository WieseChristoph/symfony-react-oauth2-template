<?php

declare(strict_types=1);

namespace App\Service\OAuth2\Client;

use App\Dto\OAuth2\UserInformation;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

interface OAuth2ClientInterface
{
    public function getProviderName(): string;

    public function getProvider(): AbstractProvider;

    public function getAuthorizationUrl(): string;

    public function getState(): string;

    /**
     * @throws IdentityProviderException
     */
    public function getAccessToken(string $code): string;

    public function getUserInfo(string $accessToken): UserInformation;
}
