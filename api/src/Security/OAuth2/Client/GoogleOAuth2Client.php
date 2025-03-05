<?php

declare(strict_types=1);

namespace App\Security\OAuth2\Client;

use App\Dto\OAuth2\UserInformation;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use UnexpectedValueException;

use function assert;

class GoogleOAuth2Client extends AbstractOAuth2Client
{
    private const GOOGLE_PROVIDER_NAME = 'google';

    public function __construct(string $clientId, string $clientSecret, string $redirectUri)
    {
        $provider = new Google([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
        ]);

        parent::__construct(self::GOOGLE_PROVIDER_NAME, $provider);
    }

    public function getUserInfo(string $accessToken): UserInformation
    {
        $resourceOwner = $this->getResourceOwner($accessToken);

        assert($resourceOwner instanceof GoogleUser);

        return new UserInformation(
            $resourceOwner->getEmail() ?? throw new UnexpectedValueException('Email must not be null'),
            $resourceOwner->getFirstName() ?? throw new UnexpectedValueException('First name must not be null'),
            $resourceOwner->getAvatar(),
        );
    }
}
