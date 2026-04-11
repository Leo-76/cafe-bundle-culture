<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Listener;

use CafeCulture\BlogBundle\Repository\ArticleRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Incrémente les vues sur les articles en cache-friendly via un cookie.
 * Évite le double-comptage sur la même session.
 */
class ViewCountListener implements EventSubscriberInterface
{
    private const COOKIE_NAME   = 'cafe_viewed';
    private const COOKIE_TTL    = 86400; // 24h

    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Ne s'applique qu'aux pages d'article
        if ($request->attributes->get('_route') !== 'cafe_culture_article_show') {
            return;
        }

        $slug    = $request->attributes->get('slug');
        $viewed  = json_decode($request->cookies->get(self::COOKIE_NAME, '[]'), true) ?? [];

        if (in_array($slug, $viewed, true)) {
            return; // Déjà vu dans la session cookie
        }

        $article = $this->articleRepository->findOneBy(['slug' => $slug, 'status' => 'published']);
        if (!$article) {
            return;
        }

        $article->incrementViews();
        $this->articleRepository->getEntityManager()->flush();

        // Mettre à jour le cookie
        $viewed[] = $slug;
        $event->getResponse()->headers->setCookie(
            new \Symfony\Component\HttpFoundation\Cookie(
                self::COOKIE_NAME,
                json_encode(array_slice($viewed, -50)), // garder les 50 derniers
                time() + self::COOKIE_TTL,
                '/',
                null,
                false,
                true,
                false,
                'lax'
            )
        );
    }
}
