<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogControllerTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return \CafeCulture\BlogBundle\Tests\App\TestKernel::class;
    }

    private static ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        self::$em = static::getContainer()->get(EntityManagerInterface::class);

        // Crée le schéma SQLite en mémoire pour les tests
        $schemaTool = new SchemaTool(self::$em);
        $metadata   = self::$em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        // Insère des données de test
        $category = (new Category())
            ->setName('Test Catégorie')
            ->setSlug('test-categorie')
            ->setColor('#6B3F2A')
            ->setIcon('fa-coffee')
            ->setPosition(0);
        self::$em->persist($category);

        $article = (new Article())
            ->setTitle('Article de test café')
            ->setSlug('article-test-cafe')
            ->setContent('Contenu de l\'article de test sur le café arabica.')
            ->setCategory($category)
            ->setAuthorName('Auteur Test')
            ->publish();
        self::$em->persist($article);

        self::$em->flush();
    }

    public function testBlogIndexReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog');
        $this->assertResponseIsSuccessful();
    }

    public function testArticleShowReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/article/article-test-cafe');
        $this->assertResponseIsSuccessful();
    }

    public function testArticleShowNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/article/slug-inexistant');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCategoryPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/categorie/test-categorie');
        $this->assertResponseIsSuccessful();
    }

    public function testCategoryNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/categorie/inexistante');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testSearchPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/recherche?q=café');
        $this->assertResponseIsSuccessful();
    }

    public function testSearchWithEmptyQueryReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/recherche');
        $this->assertResponseIsSuccessful();
    }

    public function testApiSearchEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cafe/search?q=cafe');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testApiRecentEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cafe/recent');
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('title', $data[0]);
        $this->assertArrayHasKey('slug', $data[0]);
    }

    public function testSubmitComment(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/article/article-test-cafe');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Publier mon commentaire')->form([
            'comment[authorName]'  => 'Jean Dupont',
            'comment[authorEmail]' => 'jean@test.com',
            'comment[content]'     => 'Excellent article sur le café ! Très instructif et bien documenté.',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/blog/article/article-test-cafe');
    }

    public function testTagPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/tag/arabica');
        $this->assertResponseIsSuccessful();
    }

    public function testOriginPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/origine/Éthiopie');
        $this->assertResponseIsSuccessful();
    }
}
