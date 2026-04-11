<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Tests\Entity;

use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;
use CafeCulture\BlogBundle\Repository\ArticleRepository;
use CafeCulture\BlogBundle\Service\ArticleService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArticleServiceTest extends TestCase
{
    private ArticleService $service;

    /** @var ArticleRepository&MockObject */
    private ArticleRepository $repo;

    protected function setUp(): void
    {
        $this->repo    = $this->createMock(ArticleRepository::class);
        $this->service = new ArticleService($this->repo);
    }

    private function makeArticle(string $title = 'Mon Espresso', string $content = 'Un contenu de test.'): Article
    {
        $cat = (new Category())->setName('Test')->setSlug('test');
        return (new Article())->setTitle($title)->setContent($content)->setCategory($cat);
    }

    // ── generateSlug ────────────────────────────────────────────────────────

    public function testGenerateSlugFromTitle(): void
    {
        $article = $this->makeArticle('Le Café Arabica en Éthiopie');
        $this->repo->method('findOneBy')->willReturn(null);

        $this->service->generateSlug($article);

        $this->assertNotEmpty($article->getSlug());
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $article->getSlug());
    }

    public function testGenerateSlugDoesNotOverwriteExisting(): void
    {
        $article = $this->makeArticle('Titre');
        $article->setSlug('mon-slug-existant');

        $this->repo->expects($this->never())->method('findOneBy');
        $this->service->generateSlug($article);

        $this->assertSame('mon-slug-existant', $article->getSlug());
    }

    public function testGenerateSlugAddsNumericSuffixOnCollision(): void
    {
        $existing = $this->makeArticle('Titre')->setSlug('le-cafe-arabica-en-ethiopie');
        $this->repo
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls($existing, null);

        $article = $this->makeArticle('Le Café Arabica en Éthiopie');
        $this->service->generateSlug($article);

        $this->assertStringEndsWith('-1', $article->getSlug());
    }

    // ── computeReadingTime ──────────────────────────────────────────────────

    public function testComputeReadingTimeShortContent(): void
    {
        $article = $this->makeArticle('T', 'Court texte.');
        $this->service->computeReadingTime($article, 200);
        $this->assertSame(1, $article->getReadingTimeMinutes());
    }

    public function testComputeReadingTimeLongContent(): void
    {
        $words   = implode(' ', array_fill(0, 800, 'arabica'));
        $article = $this->makeArticle('T', $words);
        $this->service->computeReadingTime($article, 200);
        $this->assertSame(4, $article->getReadingTimeMinutes());
    }

    // ── ensureExcerpt ───────────────────────────────────────────────────────

    public function testEnsureExcerptDoesNotOverwriteExisting(): void
    {
        $article = $this->makeArticle('T', 'Contenu long...');
        $article->setExcerpt('Mon extrait personnalisé.');
        $this->service->ensureExcerpt($article);
        $this->assertSame('Mon extrait personnalisé.', $article->getExcerpt());
    }

    public function testEnsureExcerptGeneratesFromContent(): void
    {
        $article = $this->makeArticle('T', 'Un texte assez court.');
        $this->service->ensureExcerpt($article, 200);
        $this->assertSame('Un texte assez court.', $article->getExcerpt());
    }

    public function testEnsureExcerptTruncatesLongContent(): void
    {
        $long    = str_repeat('Café arabica éthiopien de spécialité. ', 20);
        $article = $this->makeArticle('T', $long);
        $this->service->ensureExcerpt($article, 100);
        $this->assertLessThanOrEqual(110, mb_strlen($article->getExcerpt()));
        $this->assertStringEndsWith('…', $article->getExcerpt());
    }

    // ── ensureSeoFields ─────────────────────────────────────────────────────

    public function testEnsureSeoFieldsPopulatesMetaTitle(): void
    {
        $article = $this->makeArticle('Le V60 de Hario');
        $article->setExcerpt('Guide complet sur le V60.');
        $this->service->ensureSeoFields($article, 'Mon Blog Café');
        $this->assertStringContainsString('Le V60 de Hario', $article->getMetaTitle());
        $this->assertStringContainsString('Mon Blog Café', $article->getMetaTitle());
    }

    public function testEnsureSeoFieldsDoesNotOverwriteExisting(): void
    {
        $article = $this->makeArticle('Titre');
        $article->setMetaTitle('Mon titre SEO custom');
        $article->setMetaDescription('Ma description SEO custom.');
        $this->service->ensureSeoFields($article);
        $this->assertSame('Mon titre SEO custom', $article->getMetaTitle());
        $this->assertSame('Ma description SEO custom.', $article->getMetaDescription());
    }

    // ── prepareForPersist ───────────────────────────────────────────────────

    public function testPrepareForPersistRunsAllSteps(): void
    {
        $this->repo->method('findOneBy')->willReturn(null);

        $content = str_repeat('Espresso ristretto lungo americano. ', 50);
        $article = $this->makeArticle('Guide Espresso Complet', $content);

        $this->service->prepareForPersist($article);

        $this->assertNotEmpty($article->getSlug());
        $this->assertGreaterThanOrEqual(1, $article->getReadingTimeMinutes());
        $this->assertNotNull($article->getExcerpt());
        $this->assertNotNull($article->getMetaTitle());
        $this->assertNotNull($article->getMetaDescription());
    }
}
