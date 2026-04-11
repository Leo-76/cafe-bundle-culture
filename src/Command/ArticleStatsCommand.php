<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Command;

use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Repository\ArticleRepository;
use CafeCulture\BlogBundle\Service\ArticleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande : php bin/console cafe:articles:stats
 * Affiche les statistiques du blog dans le terminal.
 */
#[AsCommand(
    name: 'cafe:articles:stats',
    description: 'Affiche les statistiques des articles du blog café.'
)]
class ArticleStatsCommand extends Command
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly ArticleService    $articleService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('detailed', 'd', InputOption::VALUE_NONE, 'Afficher le détail par catégorie');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $stats = $this->articleService->getStats();

        $io->title('☕ CafeCultureBundle — Statistiques du blog');

        $io->table(
            ['Indicateur', 'Valeur'],
            [
                ['Total articles',   $stats['total']],
                ['Publiés',          $stats['published']],
                ['Brouillons',       $stats['draft']],
                ['Archivés',         $stats['archived']],
            ]
        );

        if ($input->getOption('detailed')) {
            $io->section('Articles les plus vus');
            $mostViewed = $this->articleRepository->findMostViewed(10);
            $rows = [];
            foreach ($mostViewed as $article) {
                $rows[] = [
                    $article->getId(),
                    mb_substr($article->getTitle(), 0, 50),
                    $article->getCategory()?->getName() ?? '—',
                    number_format($article->getViews()),
                    $article->getAverageRating() > 0 ? $article->getAverageRating() . '/5' : '—',
                ];
            }
            $io->table(['ID', 'Titre', 'Catégorie', 'Vues', 'Note'], $rows);
        }

        $io->success('Statistiques générées avec succès.');
        return Command::SUCCESS;
    }
}
