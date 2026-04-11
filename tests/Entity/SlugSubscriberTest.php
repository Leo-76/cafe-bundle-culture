<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Tests\Entity;

use CafeCulture\BlogBundle\EventSubscriber\SlugSubscriber;
use PHPUnit\Framework\TestCase;

class SlugSubscriberTest extends TestCase
{
    public function testSlugifyBasic(): void
    {
        $this->assertSame('cafe-au-lait', SlugSubscriber::slugify('Café au Lait'));
    }

    public function testSlugifyAccents(): void
    {
        $this->assertSame('ethiopie', SlugSubscriber::slugify('Éthiopie'));
    }

    public function testSlugifySpecialChars(): void
    {
        $this->assertSame('v60-hario', SlugSubscriber::slugify('V60 Hario'));
    }

    public function testSlugifyLeadingTrailingHyphens(): void
    {
        $slug = SlugSubscriber::slugify('  --Café-- ');
        $this->assertStringStartsNotWith('-', $slug);
        $this->assertStringEndsNotWith('-', $slug);
    }

    public function testSlugifyConsecutiveHyphens(): void
    {
        $slug = SlugSubscriber::slugify('Café   Arabica');
        $this->assertStringNotContainsString('--', $slug);
    }

    public function testSlugifyFrenchTitle(): void
    {
        $slug = SlugSubscriber::slugify("L'art de la torréfaction artisanale");
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $slug);
    }

    public function testSlugifyNumbers(): void
    {
        $this->assertSame('preparation-v60-hario', SlugSubscriber::slugify('Préparation V60 Hario'));
    }
}
