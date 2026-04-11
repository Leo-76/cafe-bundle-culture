<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use CafeCulture\BlogBundle\Entity\Comment;

/** @extends ServiceEntityRepository<Comment> */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findPendingApproval(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.approved = false')
            ->leftJoin('c.article', 'a')
            ->addSelect('a')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
