<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Service\ArticleService;

/**
 * Écoute les événements Doctrine sur l'entité Article pour
 * déclencher automatiquement les calculs métier (slug, temps de lecture, SEO).
 */
#[AsEntityListener(event: Events::prePersist, entity: Article::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Article::class)]
class ArticleListener
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    public function prePersist(Article $article): void
    {
        $this->articleService->prepareForPersist($article);
    }

    public function preUpdate(Article $article): void
    {
        // Recalcule le temps de lecture si le contenu a changé
        $this->articleService->computeReadingTime($article);
        // Met à jour l'extrait si vide
        $this->articleService->ensureExcerpt($article);
    }
}
