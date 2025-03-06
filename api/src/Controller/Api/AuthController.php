<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use App\Security\CookieAccessTokenExtractor;
use App\Security\OAuth2\Client\AbstractOAuth2Client;
use App\Security\OAuth2\Client\DiscordOAuth2Client;
use App\Security\OAuth2\Client\GoogleOAuth2Client;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\AccessToken\HeaderAccessTokenExtractor;

use function is_string;

#[Route(path: '/auth', name: 'auth_')]
class AuthController extends AbstractController
{
    private const OAUTH2_STATE_KEY = 'oauth2_session';
    private const REDIRECT_URL_KEY = 'redirect_url';

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
        private readonly LoggerInterface $logger,
    ) {
        $this->providers = [
            $googleClient->getProviderName() => $googleClient,
            $discordClient->getProviderName() => $discordClient,
        ];
    }

    #[Route('/logout', 'logout')]
    public function logout(Request $request): RedirectResponse
    {
        $response = $this->redirect($request->headers->get('referer') ?? '/');
        $response->headers->removeCookie(CookieAccessTokenExtractor::AUTH_COOKIE_NAME);

        $user = $this->getUser();
        if ($user === null) {
            return $response;
        }

        $token = (new CookieAccessTokenExtractor())->extractAccessToken($request)
            ?? (new HeaderAccessTokenExtractor())->extractAccessToken($request);

        $apiToken = $this->apiTokenRepository->findOneBy(['token' => $token, 'user' => $user]);
        if ($apiToken === null) {
            $this->logger->warning('No API token found for token "' . $token . '" and user ' . $user->getUserIdentifier());

            return $response;
        }

        $this->entityManager->remove($apiToken);
        $this->entityManager->flush();

        $response = $this->redirect($request->headers->get('referer') ?? '/');
        $response->headers->removeCookie(CookieAccessTokenExtractor::AUTH_COOKIE_NAME);

        return $response;
    }

    #[Route('/{provider}', 'redirect')]
    public function beginAuth(string $provider, Request $request): RedirectResponse
    {
        $oauth2Client = $this->providers[$provider] ?? null;
        if ($oauth2Client === null) {
            $this->logger->warning('OAuth2 provider ' . $provider . ' not found');

            return $this->redirect($request->headers->get('referer') ?? '/');
        }

        $authUrl = $oauth2Client->getAuthorizationUrl();
        $request->getSession()->set(self::OAUTH2_STATE_KEY, $oauth2Client->getState());
        $request->getSession()->set(self::REDIRECT_URL_KEY, $request->headers->get('referer'));

        return $this->redirect($authUrl);
    }

    #[Route('/{provider}/callback', 'callback')]
    public function callback(string $provider, Request $request): RedirectResponse
    {
        $redirectUrl = $request->getSession()->remove(self::REDIRECT_URL_KEY);
        $redirectResponse = $this->redirect(is_string($redirectUrl) ? $redirectUrl : '/');

        $oauth2Client = $this->providers[$provider] ?? null;
        if ($oauth2Client === null) {
            $this->logger->warning('OAuth2 provider ' . $provider . ' not found');

            return $redirectResponse;
        }

        $state = $request->query->get('state');
        if (!is_string($state) || $state !== $request->getSession()->remove(self::OAUTH2_STATE_KEY)) {
            $this->logger->warning('Invalid OAuth2 state "' . $state . '"');

            return $redirectResponse;
        }
        $request->getSession()->remove(self::OAUTH2_STATE_KEY);

        $code = $request->query->get('code');
        if (!is_string($code)) {
            $this->logger->warning(' OAuth2 authorization code missing');

            return $redirectResponse;
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

        $authCookie = new Cookie(
            CookieAccessTokenExtractor::AUTH_COOKIE_NAME,
            $apiToken->getToken(),
            $apiToken->getExpiresAt(),
        );

        $redirectResponse->headers->setCookie($authCookie);

        return $redirectResponse;
    }
}
