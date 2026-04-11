<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use CafeCulture\BlogBundle\Entity\Category;

/** @extends ServiceEntityRepository<Category> */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithPublishedArticles(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.articles', 'a')
            ->addSelect('a')
            ->andWhere('a.status = :status OR a.id IS NULL')
            ->setParameter('status', 'published')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
