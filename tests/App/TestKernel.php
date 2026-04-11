<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Tests\App;

use CafeCulture\BlogBundle\CafeCultureBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;

class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new CafeCultureBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'secret'      => 'test_secret',
                'test'        => true,
                'router'      => ['utf8' => true],
                'session'     => ['storage_factory_id' => 'session.storage.factory.mock_file'],
            ]);

            $container->loadFromExtension('doctrine', [
                'dbal' => ['url' => '%env(DATABASE_URL)%', 'driver' => 'pdo_sqlite'],
                'orm'  => [
                    'auto_generate_proxy_classes' => true,
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'auto_mapping'    => true,
                    'mappings'        => [
                        'CafeCultureBundle' => [
                            'is_bundle' => true,
                            'type'      => 'attribute',
                            'dir'       => 'src/Entity',
                            'prefix'    => 'CafeCulture\\BlogBundle\\Entity',
                        ],
                    ],
                ],
            ]);

            $container->loadFromExtension('twig', [
                'paths' => [__DIR__ . '/../../templates' => 'CafeCulture'],
            ]);

            $container->loadFromExtension('cafe_culture', [
                'posts_per_page'  => 5,
                'enable_comments' => true,
                'enable_ratings'  => true,
            ]);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/cafe_culture_bundle/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/cafe_culture_bundle/logs';
    }
}
