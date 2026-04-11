<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Service;

use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\EventSubscriber\SlugSubscriber;
use CafeCulture\BlogBundle\Repository\ArticleRepository;

/**
 * Service métier pour les articles du blog café.
 */
class ArticleService
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {}

    /**
     * Génère un slug unique pour un article.
     * Ajoute un suffixe numérique en cas de collision.
     */
    public function generateSlug(Article $article): void
    {
        if (!empty($article->getSlug())) {
            return;
        }

        $base   = SlugSubscriber::slugify($article->getTitle());
        $slug   = $base;
        $suffix = 1;

        while ($this->articleRepository->findOneBy(['slug' => $slug])) {
            $slug = $base . '-' . $suffix;
            ++$suffix;
        }

        $article->setSlug($slug);
    }

    /**
     * Calcule automatiquement le temps de lecture et le définit sur l'article.
     */
    public function computeReadingTime(Article $article, int $wpm = 200): void
    {
        $words   = str_word_count(strip_tags($article->getContent()));
        $minutes = (int) max(1, ceil($words / $wpm));
        $article->setReadingTimeMinutes($minutes);
    }

    /**
     * Génère l'extrait automatiquement depuis le contenu si absent.
     */
    public function ensureExcerpt(Article $article, int $length = 200): void
    {
        if ($article->getExcerpt()) {
            return;
        }

        $clean = strip_tags($article->getContent());
        if (mb_strlen($clean) <= $length) {
            $article->setExcerpt($clean);
            return;
        }

        $cut = mb_substr($clean, 0, $length);
        $pos = mb_strrpos($cut, ' ');
        $article->setExcerpt(($pos !== false ? mb_substr($cut, 0, $pos) : $cut) . '…');
    }

    /**
     * Remplit les champs SEO automatiquement si absents.
     */
    public function ensureSeoFields(Article $article, string $sitename = 'La Culture du Café'): void
    {
        if (!$article->getMetaTitle()) {
            $article->setMetaTitle(
                mb_substr($article->getTitle(), 0, 55) . ' — ' . $sitename
            );
        }

        if (!$article->getMetaDescription()) {
            $excerpt = $article->getExcerpt() ?? strip_tags($article->getContent());
            $article->setMetaDescription(mb_substr($excerpt, 0, 160));
        }
    }

    /**
     * Pipeline complet de préparation avant persistance.
     */
    public function prepareForPersist(Article $article, string $sitename = 'La Culture du Café'): void
    {
        $this->generateSlug($article);
        $this->computeReadingTime($article);
        $this->ensureExcerpt($article);
        $this->ensureSeoFields($article, $sitename);
    }

    /**
     * Retourne les statistiques globales du blog.
     */
    public function getStats(): array
    {
        return [
            'total'     => $this->articleRepository->count([]),
            'published' => $this->articleRepository->countPublished(),
            'draft'     => $this->articleRepository->count(['status' => Article::STATUS_DRAFT]),
            'archived'  => $this->articleRepository->count(['status' => Article::STATUS_ARCHIVED]),
        ];
    }
}
