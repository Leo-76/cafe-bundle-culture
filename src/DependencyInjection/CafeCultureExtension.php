<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CafeCultureExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');

        // Paramètres du bundle injectés dans le container
        $container->setParameter('cafe_culture.posts_per_page', $config['posts_per_page']);
        $container->setParameter('cafe_culture.upload_dir', $config['upload_dir']);
        $container->setParameter('cafe_culture.default_locale', $config['default_locale']);
        $container->setParameter('cafe_culture.enable_comments', $config['enable_comments']);
        $container->setParameter('cafe_culture.enable_ratings', $config['enable_ratings']);
        $container->setParameter('cafe_culture.sitename', $config['sitename']);
    }

    public function getAlias(): string
    {
        return 'cafe_culture';
    }
}
