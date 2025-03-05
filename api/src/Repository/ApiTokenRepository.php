<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    public function deleteExpiredTokens(): int
    {
        /** @var int $deletedTokenCount */
        $deletedTokenCount = $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt <= CURRENT_TIMESTAMP()')
            ->getQuery()
            ->execute()
        ;

        return $deletedTokenCount;
    }
}
