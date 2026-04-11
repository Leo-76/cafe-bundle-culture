<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use CafeCulture\BlogBundle\Entity\Tag;

/** @extends ServiceEntityRepository<Tag> */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findPopular(int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.articles', 'a')
            ->addSelect('COUNT(a.id) AS HIDDEN articleCount')
            ->groupBy('t.id')
            ->orderBy('articleCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
