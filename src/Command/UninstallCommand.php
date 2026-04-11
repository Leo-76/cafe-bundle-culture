<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Supprime proprement les fichiers generes par cafe:install.
 *
 * Usage :
 *   php bin/console cafe:uninstall
 *   php bin/console cafe:uninstall --drop-tables   # supprime aussi les tables BDD
 */
#[AsCommand(
    name: 'cafe:uninstall',
    description: 'Supprime les fichiers de configuration et assets generes par cafe:install.'
)]
class UninstallCommand extends Command
{
    private Filesystem $fs;

    public function __construct(private readonly KernelInterface $kernel)
    {
        parent::__construct();
        $this->fs = new Filesystem();
    }

    protected function configure(): void
    {
        $this
            ->addOption('drop-tables', null, InputOption::VALUE_NONE, 'Supprimer aussi les tables Doctrine (IRREVERSIBLE)')
            ->addOption('yes',         'y',  InputOption::VALUE_NONE, 'Ne pas demander de confirmation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io         = new SymfonyStyle($input, $output);
        $projectDir = $this->kernel->getProjectDir();

        $io->title('Desinstallation du CafeCultureBundle');
        $io->warning('Cette commande va supprimer les fichiers generes par cafe:install.');

        if (!$input->getOption('yes') && !$io->confirm('Continuer ?', false)) {
            $io->text('Operation annulee.');
            return Command::SUCCESS;
        }

        $removed = 0;

        // Fichiers de configuration
        $filesToRemove = [
            $projectDir . '/config/packages/cafe_culture.yaml',
            $projectDir . '/config/routes/cafe_culture.yaml',
        ];

        foreach ($filesToRemove as $file) {
            if ($this->fs->exists($file)) {
                $this->fs->remove($file);
                $io->text('<info>[OK]</info>  Supprime : ' . str_replace($projectDir . '/', '', $file));
                ++$removed;
            }
        }

        // Assets
        $assetsDir = $projectDir . '/public/bundles/cafeculturebundle';
        if ($this->fs->exists($assetsDir)) {
            $this->fs->remove($assetsDir);
            $io->text('<info>[OK]</info>  Supprime : public/bundles/cafeculturebundle/');
            ++$removed;
        }

        // Tables BDD (optionnel et dangereux)
        if ($input->getOption('drop-tables')) {
            $io->warning('Suppression des tables BDD (cafe_article, cafe_category, cafe_tag, cafe_comment, cafe_rating)...');
            if ($io->confirm('ATTENTION : toutes les donnees seront perdues. Confirmer ?', false)) {
                try {
                    $app   = $this->getApplication();
                    $cmd   = $app->find('doctrine:migrations:execute');
                    $input = new ArrayInput([
                        'command'    => 'doctrine:migrations:execute',
                        'versions'   => ['CafeCulture\BlogBundle\Migrations\Version20240101000001'],
                        '--down'     => true,
                        '--no-interaction' => true,
                    ]);
                    $input->setInteractive(false);
                    $cmd->run($input, $output);
                    $io->text('<info>[OK]</info>  Tables supprimees.');
                } catch (\Throwable $e) {
                    $io->error('Impossible de supprimer les tables : ' . $e->getMessage());
                }
            }
        }

        $io->success(sprintf('%d fichier(s) supprime(s). Pensez a retirer le bundle de config/bundles.php.', $removed));
        return Command::SUCCESS;
    }
}
