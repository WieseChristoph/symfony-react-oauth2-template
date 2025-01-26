<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\OAuth2\Client\AbstractOAuth2Client;
use App\Service\OAuth2\Client\DiscordOAuth2Client;
use App\Service\OAuth2\Client\GoogleOAuth2Client;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function is_string;

class AuthController extends AbstractController
{
    private const OAUTH2_STATE_KEY = 'oauth2session';

    /**
     * @var array<string, AbstractOAuth2Client>
     */
    private array $providers;

    public function __construct(
        GoogleOAuth2Client $googleClient,
        DiscordOAuth2Client $discordClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
        $this->providers = [
            $googleClient->getProviderName() => $googleClient,
            $discordClient->getProviderName() => $discordClient,
        ];
    }

    #[Route('/api/auth/logout', 'api_auth_logout')]
    public function logout(): JsonResponse
    {
        throw new LogicException('This method can be blank - it will be intercepted by the firewall.');
    }

    #[Route('/api/auth/{provider}', 'api_auth_redirect')]
    public function beginAuth(string $provider, Request $request): Response
    {
        $oauth2Client = $this->providers[$provider] ?? null;
        if ($oauth2Client === null) {
            return $this->json(['error' => 'Invalid provider'], 400);
        }

        $authUrl = $oauth2Client->getAuthorizationUrl();
        $request->getSession()->set(self::OAUTH2_STATE_KEY, $oauth2Client->getState());

        return $this->redirect($authUrl);
    }

    #[Route('/api/auth/{provider}/callback', 'api_auth_callback')]
    public function callback(string $provider, Request $request): JsonResponse
    {
        $oauth2Client = $this->providers[$provider] ?? null;
        if ($oauth2Client === null) {
            return $this->json(['error' => 'Invalid provider'], 400);
        }

        $state = $request->query->get('state');
        if (!is_string($state) || $state !== $request->getSession()->get(self::OAUTH2_STATE_KEY)) {
            $request->getSession()->remove(self::OAUTH2_STATE_KEY);

            return $this->json(['error' => 'Invalid state'], 400);
        }
        $request->getSession()->remove(self::OAUTH2_STATE_KEY);

        $code = $request->query->get('code');
        if (!is_string($code)) {
            return $this->json(['error' => 'Authorization code missing'], 400);
        }

        $accessToken = $oauth2Client->getAccessToken($code);
        $userInfo = $oauth2Client->getUserInfo($accessToken);

        $user = $this->userRepository->findOneBy(['email' => $userInfo->email]);

        if ($user === null) {
            $user = $userInfo->toUser();

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $this->json(['user' => $userInfo]);
    }
}
