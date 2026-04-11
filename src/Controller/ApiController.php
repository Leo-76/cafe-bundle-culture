<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Controller;

use CafeCulture\BlogBundle\Entity\Rating;
use CafeCulture\BlogBundle\Repository\ArticleRepository;
use CafeCulture\BlogBundle\Repository\RatingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cafe', name: 'cafe_culture_api_')]
class ApiController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly RatingRepository  $ratingRepository,
    ) {}

    /**
     * Soumettre une note sur un article (POST /api/cafe/rate/{id}).
     */
    #[Route('/rate/{id}', name: 'rate', methods: ['POST'])]
    public function rate(int $id, Request $request): JsonResponse
    {
        $article = $this->articleRepository->find($id);
        if (!$article) {
            return $this->json(['error' => 'Article introuvable.'], 404);
        }

        $score = (int) $request->toArray()['score'] ?? 0;
        if ($score < 1 || $score > 5) {
            return $this->json(['error' => 'La note doit être comprise entre 1 et 5.'], 422);
        }

        $ip = $request->getClientIp() ?? '0.0.0.0';
        if ($this->ratingRepository->hasAlreadyRated($article, $ip)) {
            return $this->json(['error' => 'Vous avez déjà noté cet article.'], 409);
        }

        $rating = (new Rating())
            ->setArticle($article)
            ->setScore($score)
            ->setIpAddress($ip);

        $em = $this->ratingRepository->getEntityManager();
        $em->persist($rating);
        $em->flush();

        return $this->json([
            'success' => true,
            'average' => $article->getAverageRating(),
            'count'   => $article->getRatings()->count(),
        ]);
    }

    /**
     * Retourne les articles récents (GET /api/cafe/recent).
     */
    #[Route('/recent', name: 'recent', methods: ['GET'])]
    public function recent(): JsonResponse
    {
        $articles = $this->articleRepository->findRecent(5);

        return $this->json(array_map(fn($a) => [
            'id'          => $a->getId(),
            'title'       => $a->getTitle(),
            'slug'        => $a->getSlug(),
            'publishedAt' => $a->getPublishedAt()?->format('d/m/Y'),
            'category'    => $a->getCategory()?->getName(),
            'views'       => $a->getViews(),
        ], $articles));
    }

    /**
     * Recherche AJAX (GET /api/cafe/search?q=espresso).
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query->getString('q', ''));
        if (mb_strlen($q) < 2) {
            return $this->json([]);
        }

        $results = $this->articleRepository->search($q, 8);

        return $this->json(array_map(fn($a) => [
            'id'       => $a->getId(),
            'title'    => $a->getTitle(),
            'slug'     => $a->getSlug(),
            'excerpt'  => mb_substr(strip_tags($a->getContent()), 0, 120) . '…',
            'category' => $a->getCategory()?->getName(),
        ], $results));
    }
}
