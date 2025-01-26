<?php

declare(strict_types=1);

namespace App\Service\OAuth2\Client;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

abstract class AbstractOAuth2Client implements OAuth2ClientInterface
{
    /**
     * @param array<string, mixed> $authorizationUrlOptions
     */
    public function __construct(
        protected string $providerName,
        protected AbstractProvider $provider,
        protected array $authorizationUrlOptions = [],
    ) {
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getProvider(): AbstractProvider
    {
        return $this->provider;
    }

    public function getAuthorizationUrl(): string
    {
        return $this->provider->getAuthorizationUrl($this->authorizationUrlOptions);
    }

    public function getState(): string
    {
        return $this->provider->getState();
    }

    public function getAccessToken(string $code): string
    {
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        return $accessToken->getToken();
    }

    protected function getResourceOwner(string $accessToken): ResourceOwnerInterface
    {
        $token = new AccessToken(['access_token' => $accessToken]);

        return $this->provider->getResourceOwner($token);
    }
}
