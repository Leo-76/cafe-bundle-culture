<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Controller;

use CafeCulture\BlogBundle\Entity\Comment;
use CafeCulture\BlogBundle\Form\CommentType;
use CafeCulture\BlogBundle\Repository\ArticleRepository;
use CafeCulture\BlogBundle\Repository\CategoryRepository;
use CafeCulture\BlogBundle\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/blog', name: 'cafe_culture_')]
class BlogController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository  $articleRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository      $tagRepository,
        private readonly int                $postsPerPage = 9,
    ) {}

    /**
     * Page d'accueil du blog.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page       = max(1, $request->query->getInt('page', 1));
        $featured   = $this->articleRepository->findFeatured(3);
        $articles   = $this->articleRepository->findPublished($page, $this->postsPerPage);
        $total      = $this->articleRepository->countPublished();
        $categories = $this->categoryRepository->findAllOrdered();

        return $this->render('@CafeCulture/blog/index.html.twig', [
            'featured'      => $featured,
            'articles'      => $articles,
            'categories'    => $categories,
            'current_page'  => $page,
            'total_pages'   => (int) ceil($total / $this->postsPerPage),
            'total_articles' => $total,
        ]);
    }

    /**
     * Affichage d'un article.
     */
    #[Route('/article/{slug}', name: 'article_show', methods: ['GET', 'POST'])]
    public function show(string $slug, Request $request): Response
    {
        $article = $this->articleRepository->findOneBy([
            'slug'   => $slug,
            'status' => 'published',
        ]);

        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        // Incrémenter les vues
        $this->articleRepository->getEntityManager()->persist(
            $article->incrementViews()
        );
        $this->articleRepository->getEntityManager()->flush();

        // Formulaire commentaire
        $comment = new Comment();
        $form    = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setArticle($article);
            $this->articleRepository->getEntityManager()->persist($comment);
            $this->articleRepository->getEntityManager()->flush();

            $this->addFlash('success', 'Votre commentaire a été soumis et sera publié après modération. Merci !');
            return $this->redirectToRoute('cafe_culture_article_show', ['slug' => $slug]);
        }

        $related = $this->articleRepository->findRelated($article, 3);

        return $this->render('@CafeCulture/blog/show.html.twig', [
            'article'  => $article,
            'related'  => $related,
            'form'     => $form,
        ]);
    }

    /**
     * Articles par catégorie.
     */
    #[Route('/categorie/{slug}', name: 'category', methods: ['GET'])]
    public function category(string $slug, Request $request): Response
    {
        $category = $this->categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        $page     = max(1, $request->query->getInt('page', 1));
        $articles = $this->articleRepository->findByCategory($category, $page, $this->postsPerPage);

        return $this->render('@CafeCulture/blog/category.html.twig', [
            'category'     => $category,
            'articles'     => $articles,
            'current_page' => $page,
        ]);
    }

    /**
     * Articles par tag.
     */
    #[Route('/tag/{slug}', name: 'tag', methods: ['GET'])]
    public function tag(string $slug): Response
    {
        $articles = $this->articleRepository->findByTag($slug);

        return $this->render('@CafeCulture/blog/tag.html.twig', [
            'tag_slug' => $slug,
            'articles' => $articles,
        ]);
    }

    /**
     * Recherche d'articles.
     */
    #[Route('/recherche', name: 'search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $query    = trim($request->query->getString('q', ''));
        $articles = $query ? $this->articleRepository->search($query) : [];

        return $this->render('@CafeCulture/blog/search.html.twig', [
            'query'    => $query,
            'articles' => $articles,
        ]);
    }

    /**
     * Articles par origine géographique du café.
     */
    #[Route('/origine/{origin}', name: 'origin', methods: ['GET'])]
    public function origin(string $origin): Response
    {
        $articles = $this->articleRepository->findByCoffeeOrigin($origin);

        return $this->render('@CafeCulture/blog/origin.html.twig', [
            'origin'   => $origin,
            'articles' => $articles,
        ]);
    }
}
