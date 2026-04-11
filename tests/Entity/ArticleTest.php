<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Tests\Entity;

use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;
use CafeCulture\BlogBundle\Entity\Tag;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private function createCategory(): Category
    {
        return (new Category())
            ->setName('Origines')
            ->setSlug('origines');
    }

    public function testArticleDefaults(): void
    {
        $article = new Article();
        $this->assertSame(Article::STATUS_DRAFT, $article->getStatus());
        $this->assertFalse($article->isFeatured());
        $this->assertSame(0, $article->getViews());
        $this->assertCount(0, $article->getTags());
        $this->assertCount(0, $article->getComments());
        $this->assertCount(0, $article->getRatings());
    }

    public function testPublish(): void
    {
        $article = new Article();
        $article->publish();
        $this->assertTrue($article->isPublished());
        $this->assertSame(Article::STATUS_PUBLISHED, $article->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $article->getPublishedAt());
    }

    public function testIncrementViews(): void
    {
        $article = new Article();
        $article->incrementViews()->incrementViews()->incrementViews();
        $this->assertSame(3, $article->getViews());
    }

    public function testAverageRatingEmpty(): void
    {
        $article = new Article();
        $this->assertSame(0.0, $article->getAverageRating());
    }

    public function testAddRemoveTag(): void
    {
        $article = new Article();
        $tag = (new Tag())->setName('Espresso')->setSlug('espresso');
        $article->addTag($tag);
        $this->assertCount(1, $article->getTags());
        $article->addTag($tag); // duplicate — doit être ignoré
        $this->assertCount(1, $article->getTags());
        $article->removeTag($tag);
        $this->assertCount(0, $article->getTags());
    }

    public function testCategoryAssociation(): void
    {
        $article  = new Article();
        $category = $this->createCategory();
        $article->setCategory($category);
        $this->assertSame('Origines', $article->getCategory()->getName());
    }

    public function testAromaProfile(): void
    {
        $article = new Article();
        $aromas  = ['fruité', 'floral', 'chocolaté'];
        $article->setAromaProfile($aromas);
        $this->assertSame($aromas, $article->getAromaProfile());
    }

    public function testMetaFields(): void
    {
        $article = new Article();
        $article
            ->setMetaTitle('Mon titre SEO')
            ->setMetaDescription('Ma description SEO pour Google');

        $this->assertSame('Mon titre SEO', $article->getMetaTitle());
        $this->assertSame('Ma description SEO pour Google', $article->getMetaDescription());
    }
}
