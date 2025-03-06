<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/users', name: 'user_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    #[Route(path: '/me', name: 'me', methods: ['GET'])]
    public function getMe(): JsonResponse
    {
        return $this->json(
            $this->normalizer->normalize(
                $this->getUser(),
                context: ['groups' => 'user:read'],
            ),
        );
    }
}
