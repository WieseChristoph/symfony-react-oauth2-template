<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

readonly class ApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private ApiTokenRepository $apiTokenRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $token = $this->apiTokenRepository->findOneBy(['token' => $accessToken]);
        if ($token === null) {
            throw new BadCredentialsException();
        }

        if (!$token->isValid()) {
            throw new CustomUserMessageAuthenticationException('Token expired');
        }

        $user = $token->getUser();
        if ($user === null) {
            throw new CustomUserMessageAuthenticationException('No user associated with the token.');
        }

        if ($token->shouldRefreshExpiry()) {
            $token->refreshExpiry();
            $this->entityManager->flush();
        }

        return new UserBadge($user->getUserIdentifier());
    }
}
