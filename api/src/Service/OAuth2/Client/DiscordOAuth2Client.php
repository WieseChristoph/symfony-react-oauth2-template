<?php

declare(strict_types=1);

namespace App\Service\OAuth2\Client;

use App\Dto\OAuth2\UserInformation;
use UnexpectedValueException;
use Wohali\OAuth2\Client\Provider\Discord;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

use function assert;
use function sprintf;

class DiscordOAuth2Client extends AbstractOAuth2Client
{
    private const DISCORD_PROVIDER_NAME = 'discord';

    public function __construct(string $clientId, string $clientSecret, string $redirectUri)
    {
        $provider = new Discord([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
        ]);

        $authorisationUrlOptions = [
            'scope' => ['identify', 'email'],
        ];

        parent::__construct(self::DISCORD_PROVIDER_NAME, $provider, $authorisationUrlOptions);
    }

    public function getUserInfo(string $accessToken): UserInformation
    {
        $resourceOwner = $this->getResourceOwner($accessToken);

        assert($resourceOwner instanceof DiscordResourceOwner);

        return new UserInformation(
            $resourceOwner->getEmail() ?? throw new UnexpectedValueException('Email must not be null'),
            $resourceOwner->getUsername() ?? throw new UnexpectedValueException('Username must not be null'),
            $this->getAvatarUrl($resourceOwner->getId(), $resourceOwner->getAvatarHash()),
        );
    }

    private function getAvatarUrl(?string $userId, ?string $avatarHash): ?string
    {
        if ($userId === null || $avatarHash === null) {
            return null;
        }

        return sprintf(
            'https://cdn.discordapp.com/avatars/%s/%s.png',
            $userId,
            $avatarHash,
        );
    }
}
