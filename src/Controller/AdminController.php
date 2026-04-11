<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;
use CafeCulture\BlogBundle\Entity\Comment;
use CafeCulture\BlogBundle\Form\ArticleType;
use CafeCulture\BlogBundle\Form\CategoryType;
use CafeCulture\BlogBundle\Repository\ArticleRepository;
use CafeCulture\BlogBundle\Repository\CategoryRepository;
use CafeCulture\BlogBundle\Repository\CommentRepository;
use CafeCulture\BlogBundle\Service\ImageUploadService;
use CafeCulture\BlogBundle\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/cafe', name: 'cafe_culture_admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ArticleRepository      $articleRepo,
        private readonly CategoryRepository     $categoryRepo,
        private readonly CommentRepository      $commentRepo,
        private readonly ImageUploadService     $imageUpload,
        private readonly ArticleService         $articleService,
    ) {}

    // ── Dashboard ─────────────────────────────────────────────────────────

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('@CafeCulture/admin/dashboard.html.twig', [
            'stats' => [
                'articles_total'     => $this->articleRepo->count([]),
                'articles_published' => $this->articleRepo->countPublished(),
                'articles_draft'     => $this->articleRepo->count(['status' => Article::STATUS_DRAFT]),
                'categories'         => $this->categoryRepo->count([]),
                'comments_pending'   => count($this->commentRepo->findPendingApproval()),
                'most_viewed'        => $this->articleRepo->findMostViewed(5),
                'recent'             => $this->articleRepo->findRecent(5),
            ],
        ]);
    }

    // ── Articles ──────────────────────────────────────────────────────────

    #[Route('/articles', name: 'articles_index', methods: ['GET'])]
    public function articlesIndex(Request $request): Response
    {
        $page     = max(1, $request->query->getInt('page', 1));
        $status   = $request->query->getString('status', '');
        $articles = $status
            ? $this->articleRepo->findBy(['status' => $status], ['createdAt' => 'DESC'], 20, ($page - 1) * 20)
            : $this->articleRepo->findBy([], ['createdAt' => 'DESC'], 20, ($page - 1) * 20);

        return $this->render('@CafeCulture/admin/articles/index.html.twig', [
            'articles'     => $articles,
            'current_page' => $page,
            'status_filter' => $status,
            'statuses'     => [
                Article::STATUS_DRAFT, Article::STATUS_PUBLISHED, Article::STATUS_ARCHIVED,
            ],
        ]);
    }

    #[Route('/articles/nouveau', name: 'article_new', methods: ['GET', 'POST'])]
    public function articleNew(Request $request): Response
    {
        $article = new Article();
        $form    = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement arômes (champ non mappé)
            $aromaText = $form->get('aromaProfileText')->getData();
            if ($aromaText) {
                $article->setAromaProfile(
                    array_map('trim', explode(',', $aromaText))
                );
            }

            // Upload image
            $imageFile = $form->get('coverImageFile')->getData();
            if ($imageFile) {
                $filename = $this->imageUpload->upload($imageFile, 'articles');
                $article->setCoverImage($filename);
            }

            // Génération auto du slug
            $this->articleService->generateSlug($article);

            $this->em->persist($article);
            $this->em->flush();

            $this->addFlash('success', 'Article « ' . $article->getTitle() . ' » créé avec succès !');
            return $this->redirectToRoute('cafe_culture_admin_articles_index');
        }

        return $this->render('@CafeCulture/admin/articles/form.html.twig', [
            'form'    => $form,
            'article' => $article,
            'mode'    => 'create',
        ]);
    }

    #[Route('/articles/{id}/modifier', name: 'article_edit', methods: ['GET', 'POST'])]
    public function articleEdit(int $id, Request $request): Response
    {
        $article = $this->articleRepo->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        // Pré-remplir le champ arômes (non mappé)
        $form = $this->createForm(ArticleType::class, $article);
        if ($article->getAromaProfile()) {
            $form->get('aromaProfileText')->setData(implode(', ', $article->getAromaProfile()));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $aromaText = $form->get('aromaProfileText')->getData();
            $article->setAromaProfile(
                $aromaText ? array_map('trim', explode(',', $aromaText)) : null
            );

            $imageFile = $form->get('coverImageFile')->getData();
            if ($imageFile) {
                $filename = $this->imageUpload->upload($imageFile, 'articles');
                $article->setCoverImage($filename);
            }

            $this->em->flush();

            $this->addFlash('success', 'Article mis à jour avec succès.');
            return $this->redirectToRoute('cafe_culture_admin_articles_index');
        }

        return $this->render('@CafeCulture/admin/articles/form.html.twig', [
            'form'    => $form,
            'article' => $article,
            'mode'    => 'edit',
        ]);
    }

    #[Route('/articles/{id}/publier', name: 'article_publish', methods: ['POST'])]
    public function articlePublish(int $id, Request $request): Response
    {
        $article = $this->articleRepo->find($id);
        if (!$article || !$this->isCsrfTokenValid('publish_' . $id, $request->request->getString('_token'))) {
            throw $this->createNotFoundException();
        }

        $article->publish();
        $this->em->flush();

        $this->addFlash('success', 'Article publié avec succès.');
        return $this->redirectToRoute('cafe_culture_admin_articles_index');
    }

    #[Route('/articles/{id}/supprimer', name: 'article_delete', methods: ['POST'])]
    public function articleDelete(int $id, Request $request): Response
    {
        $article = $this->articleRepo->find($id);
        if (!$article || !$this->isCsrfTokenValid('delete_' . $id, $request->request->getString('_token'))) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($article);
        $this->em->flush();

        $this->addFlash('success', 'Article supprimé.');
        return $this->redirectToRoute('cafe_culture_admin_articles_index');
    }

    // ── Catégories ────────────────────────────────────────────────────────

    #[Route('/categories', name: 'categories_index', methods: ['GET'])]
    public function categoriesIndex(): Response
    {
        return $this->render('@CafeCulture/admin/categories/index.html.twig', [
            'categories' => $this->categoryRepo->findAllOrdered(),
        ]);
    }

    #[Route('/categories/nouvelle', name: 'category_new', methods: ['GET', 'POST'])]
    public function categoryNew(Request $request): Response
    {
        $category = new Category();
        $form     = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($category);
            $this->em->flush();
            $this->addFlash('success', 'Catégorie créée.');
            return $this->redirectToRoute('cafe_culture_admin_categories_index');
        }

        return $this->render('@CafeCulture/admin/categories/form.html.twig', [
            'form'     => $form,
            'category' => $category,
            'mode'     => 'create',
        ]);
    }

    #[Route('/categories/{id}/modifier', name: 'category_edit', methods: ['GET', 'POST'])]
    public function categoryEdit(int $id, Request $request): Response
    {
        $category = $this->categoryRepo->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Catégorie mise à jour.');
            return $this->redirectToRoute('cafe_culture_admin_categories_index');
        }

        return $this->render('@CafeCulture/admin/categories/form.html.twig', [
            'form'     => $form,
            'category' => $category,
            'mode'     => 'edit',
        ]);
    }

    // ── Commentaires ──────────────────────────────────────────────────────

    #[Route('/commentaires', name: 'comments_index', methods: ['GET'])]
    public function commentsIndex(): Response
    {
        return $this->render('@CafeCulture/admin/comments/index.html.twig', [
            'pending'  => $this->commentRepo->findPendingApproval(),
        ]);
    }

    #[Route('/commentaires/{id}/approuver', name: 'comment_approve', methods: ['POST'])]
    public function commentApprove(int $id, Request $request): Response
    {
        $comment = $this->em->find(Comment::class, $id);
        if (!$comment || !$this->isCsrfTokenValid('approve_' . $id, $request->request->getString('_token'))) {
            throw $this->createNotFoundException();
        }

        $comment->setApproved(true);
        $this->em->flush();

        $this->addFlash('success', 'Commentaire approuvé.');
        return $this->redirectToRoute('cafe_culture_admin_comments_index');
    }

    #[Route('/commentaires/{id}/supprimer', name: 'comment_delete', methods: ['POST'])]
    public function commentDelete(int $id, Request $request): Response
    {
        $comment = $this->em->find(Comment::class, $id);
        if (!$comment || !$this->isCsrfTokenValid('delete_comment_' . $id, $request->request->getString('_token'))) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($comment);
        $this->em->flush();

        $this->addFlash('success', 'Commentaire supprimé.');
        return $this->redirectToRoute('cafe_culture_admin_comments_index');
    }
}
