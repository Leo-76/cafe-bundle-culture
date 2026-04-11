<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;
use CafeCulture\BlogBundle\Entity\Tag;

/**
 * Génère automatiquement les slugs avant la persistance en base de données.
 */
#[AsEntityListener(event: Events::prePersist, entity: Article::class)]
#[AsEntityListener(event: Events::prePersist, entity: Category::class)]
#[AsEntityListener(event: Events::prePersist, entity: Tag::class)]
class SlugSubscriber
{
    /**
     * Génère un slug URL-safe à partir d'une chaîne UTF-8.
     */
    public static function slugify(string $text): string
    {
        // Translittération unicode → ASCII
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        // Supprime les caractères non alphanumériques (sauf tiret)
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text ?? '');
        // Réduit les tirets multiples
        $text = preg_replace('/-+/', '-', $text ?? '');
        return trim($text, '-');
    }

    public function prePersist(Article $article): void
    {
        if (empty($article->getSlug())) {
            $article->setSlug(self::slugify($article->getTitle()));
        }
    }
}

/**
 * Listener séparé pour Category.
 */
#[AsEntityListener(event: Events::prePersist, entity: Category::class)]
class CategorySlugSubscriber
{
    public function prePersist(Category $category): void
    {
        if (empty($category->getSlug())) {
            $category->setSlug(SlugSubscriber::slugify($category->getName()));
        }
    }
}

/**
 * Listener séparé pour Tag.
 */
#[AsEntityListener(event: Events::prePersist, entity: Tag::class)]
class TagSlugSubscriber
{
    public function prePersist(Tag $tag): void
    {
        if (empty($tag->getSlug())) {
            $tag->setSlug(SlugSubscriber::slugify($tag->getName()));
        }
    }
}
