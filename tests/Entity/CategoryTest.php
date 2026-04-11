<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Tests\Entity;

use CafeCulture\BlogBundle\Entity\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $cat = new Category();
        $this->assertSame(0, $cat->getPosition());
        $this->assertNull($cat->getId());
        $this->assertNull($cat->getColor());
        $this->assertNull($cat->getIcon());
    }

    public function testFluentSetters(): void
    {
        $cat = (new Category())
            ->setName('Torréfaction')
            ->setSlug('torrefaction')
            ->setColor('#C0392B')
            ->setIcon('fa-fire')
            ->setPosition(1)
            ->setDescription('L\'art de torréfier le café.');

        $this->assertSame('Torréfaction', $cat->getName());
        $this->assertSame('torrefaction', $cat->getSlug());
        $this->assertSame('#C0392B', $cat->getColor());
        $this->assertSame('fa-fire', $cat->getIcon());
        $this->assertSame(1, $cat->getPosition());
        $this->assertSame('L\'art de torréfier le café.', $cat->getDescription());
    }

    public function testToString(): void
    {
        $cat = (new Category())->setName('Recettes');
        $this->assertSame('Recettes', (string) $cat);
    }

    public function testPublishedArticlesCountEmptyCollection(): void
    {
        $cat = new Category();
        $this->assertSame(0, $cat->getPublishedArticlesCount());
    }

    public function testPredefinedCategoriesExist(): void
    {
        $this->assertArrayHasKey('origines',     Category::CATEGORIES);
        $this->assertArrayHasKey('torrefaction', Category::CATEGORIES);
        $this->assertArrayHasKey('preparations', Category::CATEGORIES);
        $this->assertArrayHasKey('recettes',     Category::CATEGORIES);
        $this->assertArrayHasKey('histoire',     Category::CATEGORIES);
        $this->assertArrayHasKey('materiel',     Category::CATEGORIES);
    }

    public function testPredefinedCategoryHasRequiredKeys(): void
    {
        foreach (Category::CATEGORIES as $slug => $data) {
            $this->assertArrayHasKey('label', $data, "Missing 'label' in category '$slug'");
            $this->assertArrayHasKey('icon',  $data, "Missing 'icon' in category '$slug'");
            $this->assertArrayHasKey('color', $data, "Missing 'color' in category '$slug'");
        }
    }
}
