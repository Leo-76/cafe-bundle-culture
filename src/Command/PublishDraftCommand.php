<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Repository\ArticleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande : php bin/console cafe:articles:publish [--id=X] [--all-drafts]
 * Publie un ou plusieurs articles en brouillon.
 */
#[AsCommand(
    name: 'cafe:articles:publish',
    description: 'Publie un article brouillon via son ID ou liste tous les brouillons.'
)]
class PublishDraftCommand extends Command
{
    public function __construct(
        private readonly ArticleRepository     $articleRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::OPTIONAL, 'ID de l\'article à publier')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'Lister tous les brouillons')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Publier tous les brouillons (attention !)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('☕ Gestionnaire de publication');

        $drafts = $this->articleRepository->findBy(
            ['status' => Article::STATUS_DRAFT],
            ['createdAt' => 'DESC']
        );

        // ── Lister les brouillons ──────────────────────────────────────────
        if ($input->getOption('list') || (!$input->getArgument('id') && !$input->getOption('all'))) {
            if (empty($drafts)) {
                $io->info('Aucun brouillon trouvé.');
                return Command::SUCCESS;
            }

            $io->section(sprintf('%d brouillon(s) trouvé(s)', count($drafts)));
            $rows = [];
            foreach ($drafts as $draft) {
                $rows[] = [
                    $draft->getId(),
                    mb_substr($draft->getTitle(), 0, 55),
                    $draft->getCategory()?->getName() ?? '—',
                    $draft->getCreatedAt()->format('d/m/Y'),
                ];
            }
            $io->table(['ID', 'Titre', 'Catégorie', 'Créé le'], $rows);
            $io->note('Utilisez --id=X pour publier un article ou --all pour tout publier.');
            return Command::SUCCESS;
        }

        // ── Publier tous les brouillons ────────────────────────────────────
        if ($input->getOption('all')) {
            if (empty($drafts)) {
                $io->info('Aucun brouillon à publier.');
                return Command::SUCCESS;
            }

            if (!$io->confirm(sprintf('Publier les %d brouillons ? Cette action est irréversible.', count($drafts)), false)) {
                $io->warning('Opération annulée.');
                return Command::SUCCESS;
            }

            foreach ($drafts as $draft) {
                $draft->publish();
                $io->writeln(sprintf('  ✓ Publié : <info>%s</info>', $draft->getTitle()));
            }

            $this->em->flush();
            $io->success(sprintf('%d article(s) publié(s).', count($drafts)));
            return Command::SUCCESS;
        }

        // ── Publier un article spécifique ──────────────────────────────────
        $id      = (int) $input->getArgument('id');
        $article = $this->articleRepository->find($id);

        if (!$article) {
            $io->error("Article #$id introuvable.");
            return Command::FAILURE;
        }

        if ($article->isPublished()) {
            $io->warning("L'article #$id est déjà publié.");
            return Command::SUCCESS;
        }

        $article->publish();
        $this->em->flush();

        $io->success(sprintf('Article #%d « %s » publié avec succès !', $id, $article->getTitle()));
        return Command::SUCCESS;
    }
}
