<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use App\Security\OAuth2\Client\AbstractOAuth2Client;
use App\Security\OAuth2\Client\DiscordOAuth2Client;
use App\Security\OAuth2\Client\GoogleOAuth2Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function is_string;

#[Route(path: '/auth', name: 'auth_')]
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
        private readonly ApiTokenRepository $apiTokenRepository,
    ) {
        $this->providers = [
            $googleClient->getProviderName() => $googleClient,
            $discordClient->getProviderName() => $discordClient,
        ];
    }

    #[Route('/logout', 'logout')]
    public function logout(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if ($user === null) {
            return $this->json(['success' => false, 'error' => 'User not found'], 500);
        }

        $authorizationHeader = $request->headers->get('Authorization');

        $bearerToken = null;
        if ($authorizationHeader !== null && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            $bearerToken = $matches[1];
        }

        $apiToken = $this->apiTokenRepository->findOneBy(['token' => $bearerToken, 'user' => $user]);
        if ($apiToken === null) {
            return $this->json(['success' => false, 'error' => 'API token not found'], 500);
        }

        $this->entityManager->remove($apiToken);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{provider}', 'redirect')]
    public function beginAuth(string $provider, Request $request): Response
    {
        $oauth2Client = $this->providers[$provider] ?? null;
        if ($oauth2Client === null) {
            return $this->json(['success' => false, 'error' => 'Invalid provider'], 400);
        }

        $authUrl = $oauth2Client->getAuthorizationUrl();
        $request->getSession()->set(self::OAUTH2_STATE_KEY, $oauth2Client->getState());

        return $this->redirect($authUrl);
    }

    #[Route('/{provider}/callback', 'callback')]
    public function callback(string $provider, Request $request): JsonResponse
    {
        $oauth2Client = $this->providers[$provider] ?? null;
        if ($oauth2Client === null) {
            return $this->json(['success' => false, 'error' => 'Invalid provider'], 400);
        }

        $state = $request->query->get('state');
        if (!is_string($state) || $state !== $request->getSession()->get(self::OAUTH2_STATE_KEY)) {
            $request->getSession()->remove(self::OAUTH2_STATE_KEY);

            return $this->json(['success' => false, 'error' => 'Invalid state'], 400);
        }
        $request->getSession()->remove(self::OAUTH2_STATE_KEY);

        $code = $request->query->get('code');
        if (!is_string($code)) {
            return $this->json(['success' => false, 'error' => 'Authorization code missing'], 400);
        }

        $accessToken = $oauth2Client->getAccessToken($code);
        $userInfo = $oauth2Client->getUserInfo($accessToken);

        $user = $this->userRepository->findOneBy(['email' => $userInfo->email]);

        if ($user === null) {
            $user = $userInfo->toUser();

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $apiToken = (new ApiToken())
            ->setUser($user)
        ;

        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();

        return $this->json(['success' => true, 'token' => $apiToken->getToken()]);
    }
}
