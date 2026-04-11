<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle;

use CafeCulture\BlogBundle\DependencyInjection\CafeCultureExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * CafeCultureBundle - Bundle Symfony pour un blog dédié à la culture du café.
 *
 * Ce bundle fournit toutes les fonctionnalités nécessaires pour gérer un blog
 * complet sur la culture du café : articles, catégories, recettes, origines,
 * méthodes de préparation et bien plus encore.
 *
 * @author CafeCulture Team <hello@cafe-culture.dev>
 */
class CafeCultureBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new CafeCultureExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
