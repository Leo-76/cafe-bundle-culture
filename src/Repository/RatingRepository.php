<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Rating;

/** @extends ServiceEntityRepository<Rating> */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function hasAlreadyRated(Article $article, string $ip): bool
    {
        return (bool) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.article = :article')
            ->andWhere('r.ipAddress = :ip')
            ->setParameter('article', $article)
            ->setParameter('ip', $ip)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
