<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Retourne les articles publiés paginés.
     */
    public function findPublished(int $page = 1, int $perPage = 9): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->leftJoin('a.category', 'c')
            ->addSelect('c')
            ->leftJoin('a.tags', 't')
            ->addSelect('t')
            ->orderBy('a.publishedAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les articles mis en avant.
     */
    public function findFeatured(int $limit = 3): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.featured = true')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->leftJoin('a.category', 'c')
            ->addSelect('c')
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les articles par catégorie.
     */
    public function findByCategory(Category $category, int $page = 1, int $perPage = 9): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.category = :category')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->setParameter('category', $category)
            ->leftJoin('a.tags', 't')
            ->addSelect('t')
            ->orderBy('a.publishedAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche full-text dans les articles.
     */
    public function search(string $query, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.title LIKE :q OR a.content LIKE :q OR a.excerpt LIKE :q OR a.coffeeOrigin LIKE :q')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->setParameter('q', '%' . $query . '%')
            ->leftJoin('a.category', 'c')
            ->addSelect('c')
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Articles récents (sidebar).
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->leftJoin('a.category', 'c')
            ->addSelect('c')
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Articles populaires (les plus vus).
     */
    public function findMostViewed(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->orderBy('a.views', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Articles par tag.
     */
    public function findByTag(string $tagSlug, int $limit = 9): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('t.slug = :tag')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->setParameter('tag', $tagSlug)
            ->leftJoin('a.tags', 't')
            ->leftJoin('a.category', 'c')
            ->addSelect('c')
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Articles par origine géographique du café.
     */
    public function findByCoffeeOrigin(string $origin): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.coffeeOrigin = :origin')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->setParameter('origin', $origin)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Comptage total des articles publiés.
     */
    public function countPublished(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.status = :status')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Articles similaires (même catégorie, excluant l'article courant).
     */
    public function findRelated(Article $article, int $limit = 3): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.category = :category')
            ->andWhere('a.id != :id')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->setParameter('category', $article->getCategory())
            ->setParameter('id', $article->getId())
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
