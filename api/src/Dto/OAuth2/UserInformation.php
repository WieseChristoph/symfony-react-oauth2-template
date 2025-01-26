<?php

declare(strict_types=1);

namespace App\Dto\OAuth2;

use App\Entity\User;

class UserInformation
{
    public function __construct(
        public string $email,
        public string $username,
        public ?string $avatarUrl = null,
    ) {
    }

    public function toUser(): User
    {
        return (new User())
            ->setEmail($this->email)
            ->setUsername($this->username)
            ->setAvatarUrl($this->avatarUrl)
        ;
    }
}
