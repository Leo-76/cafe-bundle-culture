<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Controller;

use CafeCulture\BlogBundle\Repository\ArticleRepository;
use CafeCulture\BlogBundle\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository  $articleRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    /**
     * Génère le sitemap XML pour le SEO.
     * Accessible sur /blog/sitemap.xml
     */
    #[Route('/blog/sitemap.xml', name: 'cafe_culture_sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        $urls = [];

        // Page d'accueil
        $urls[] = [
            'loc'        => $this->urlGenerator->generate('cafe_culture_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'changefreq' => 'daily',
            'priority'   => '1.0',
            'lastmod'    => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        // Catégories
        foreach ($this->categoryRepository->findAllOrdered() as $category) {
            $urls[] = [
                'loc'        => $this->urlGenerator->generate('cafe_culture_category', ['slug' => $category->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
                'lastmod'    => (new \DateTimeImmutable())->format('Y-m-d'),
            ];
        }

        // Articles publiés
        $articles = $this->articleRepository->findPublished(1, 500);
        foreach ($articles as $article) {
            $urls[] = [
                'loc'        => $this->urlGenerator->generate('cafe_culture_article_show', ['slug' => $article->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'monthly',
                'priority'   => $article->isFeatured() ? '0.9' : '0.7',
                'lastmod'    => ($article->getUpdatedAt() ?? $article->getPublishedAt() ?? $article->getCreatedAt())->format('Y-m-d'),
            ];
        }

        $xml = $this->renderView('@CafeCulture/sitemap/sitemap.xml.twig', ['urls' => $urls]);

        return new Response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /**
     * Flux RSS des derniers articles.
     * Accessible sur /blog/rss.xml
     */
    #[Route('/blog/rss.xml', name: 'cafe_culture_rss', methods: ['GET'])]
    public function rss(): Response
    {
        $articles = $this->articleRepository->findRecent(20);

        $xml = $this->renderView('@CafeCulture/sitemap/rss.xml.twig', [
            'articles' => $articles,
            'feedUrl'  => $this->urlGenerator->generate('cafe_culture_rss', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'siteUrl'  => $this->urlGenerator->generate('cafe_culture_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new Response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
