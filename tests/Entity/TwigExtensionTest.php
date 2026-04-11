<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Tests\Entity;

use CafeCulture\BlogBundle\Twig\CafeCultureExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use CafeCulture\BlogBundle\Repository\ArticleRepository;
use CafeCulture\BlogBundle\Repository\CategoryRepository;
use CafeCulture\BlogBundle\Repository\TagRepository;

class TwigExtensionTest extends TestCase
{
    private CafeCultureExtension $extension;

    protected function setUp(): void
    {
        /** @var ArticleRepository&MockObject $articleRepo */
        $articleRepo = $this->createMock(ArticleRepository::class);
        $articleRepo->method('findRecent')->willReturn([]);
        $articleRepo->method('findMostViewed')->willReturn([]);

        /** @var CategoryRepository&MockObject $categoryRepo */
        $categoryRepo = $this->createMock(CategoryRepository::class);
        $categoryRepo->method('findAllOrdered')->willReturn([]);

        /** @var TagRepository&MockObject $tagRepo */
        $tagRepo = $this->createMock(TagRepository::class);
        $tagRepo->method('findPopular')->willReturn([]);

        $this->extension = new CafeCultureExtension($articleRepo, $categoryRepo, $tagRepo);
    }

    public function testReadingTimeSingleWord(): void
    {
        $result = $this->extension->readingTime('café', 200);
        $this->assertSame('1 min de lecture', $result);
    }

    public function testReadingTimeLongText(): void
    {
        $words  = implode(' ', array_fill(0, 600, 'arabica'));
        $result = $this->extension->readingTime($words, 200);
        $this->assertSame('3 min de lecture', $result);
    }

    public function testMakeExcerptShort(): void
    {
        $text   = 'Un café délicieux.';
        $result = $this->extension->makeExcerpt($text, 160);
        $this->assertSame($text, $result);
    }

    public function testMakeExcerptTruncates(): void
    {
        $text   = str_repeat('arabica ', 50);
        $result = $this->extension->makeExcerpt($text, 50);
        $this->assertLessThanOrEqual(55, mb_strlen($result));
        $this->assertStringEndsWith('…', $result);
    }

    public function testToStarsFull(): void
    {
        $html = $this->extension->toStars(5.0);
        $this->assertStringContainsString('fa-star', $html);
        $this->assertStringContainsString('text-amber', $html);
        $this->assertStringContainsString('5/5', $html);
    }

    public function testToStarsHalf(): void
    {
        $html = $this->extension->toStars(3.5);
        $this->assertStringContainsString('fa-star-half-alt', $html);
        $this->assertStringContainsString('3.5/5', $html);
    }

    public function testCoffeeOriginFlagKnown(): void
    {
        $html = $this->extension->coffeeOriginFlag('Éthiopie');
        $this->assertStringContainsString('🇪🇹', $html);
        $this->assertStringContainsString('coffee-origin-flag', $html);
    }

    public function testCoffeeOriginFlagUnknown(): void
    {
        $html = $this->extension->coffeeOriginFlag('Pays inconnu');
        $this->assertStringContainsString('☕', $html);
    }

    public function testAromaBadge(): void
    {
        $html = $this->extension->aromaBadge('chocolaté');
        $this->assertStringContainsString('aroma-badge', $html);
        $this->assertStringContainsString('chocolaté', $html);
        $this->assertStringContainsString('#6B3F2A', $html);
    }

    public function testAromaBadgeUnknownAroma(): void
    {
        $html = $this->extension->aromaBadge('mystérieux');
        $this->assertStringContainsString('aroma-badge', $html);
        $this->assertStringContainsString('mystérieux', $html);
        // Couleur par défaut café
        $this->assertStringContainsString('#6B3F2A', $html);
    }
}
