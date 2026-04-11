<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cafe_culture');

        $treeBuilder->getRootNode()
            ->children()
                ->integerNode('posts_per_page')
                    ->defaultValue(9)
                    ->min(1)
                    ->max(50)
                    ->info('Nombre d\'articles par page')
                ->end()
                ->scalarNode('upload_dir')
                    ->defaultValue('%kernel.project_dir%/public/uploads/cafe-culture')
                    ->info('Répertoire d\'upload des images')
                ->end()
                ->scalarNode('default_locale')
                    ->defaultValue('fr')
                    ->info('Locale par défaut')
                ->end()
                ->booleanNode('enable_comments')
                    ->defaultTrue()
                    ->info('Activer les commentaires sur les articles')
                ->end()
                ->booleanNode('enable_ratings')
                    ->defaultTrue()
                    ->info('Activer les notes/avis sur les articles')
                ->end()
                ->scalarNode('sitename')
                    ->defaultValue('La Culture du Café')
                    ->info('Nom du site')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
