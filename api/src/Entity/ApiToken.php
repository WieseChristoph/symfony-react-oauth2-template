<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
#[ORM\Index(columns: ['token'])]
class ApiToken
{
    private const EXPIRY_DURATION_SECONDS = 2629800 ;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private CarbonImmutable $expiresAt;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private string $token;

    public function __construct()
    {
        $this->token = bin2hex(random_bytes(32));
        $this->refreshExpiry();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getExpiresAt(): CarbonImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(CarbonImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function isValid(): bool
    {
        return $this->expiresAt->isFuture();
    }

    public function shouldRefreshExpiry(): bool
    {
        return $this->isValid() && CarbonImmutable::now()->isAfter($this->expiresAt->subSeconds(self::EXPIRY_DURATION_SECONDS / 2));
    }

    public function refreshExpiry(): void
    {
        $this->setExpiresAt(CarbonImmutable::now()->addSeconds(self::EXPIRY_DURATION_SECONDS));
    }
}
