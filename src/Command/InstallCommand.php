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
 * Commande d'installation du CafeCultureBundle dans un projet Symfony existant.
 *
 * Usage :
 *   php bin/console cafe:install
 *   php bin/console cafe:install --fixtures      # charge aussi les données de demo
 *   php bin/console cafe:install --force         # écrase les fichiers existants
 *   php bin/console cafe:install --no-migrate    # saute la migration Doctrine
 */
#[AsCommand(
    name: 'cafe:install',
    description: 'Installe le CafeCultureBundle dans votre projet Symfony (config, routes, assets, migration).'
)]
class InstallCommand extends Command
{
    private Filesystem $fs;

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
        $this->fs = new Filesystem();
    }

    protected function configure(): void
    {
        $this
            ->addOption('fixtures',    null, InputOption::VALUE_NONE, 'Charger les fixtures de demonstration apres installation')
            ->addOption('force',       null, InputOption::VALUE_NONE, 'Ecraser les fichiers de configuration existants')
            ->addOption('no-migrate',  null, InputOption::VALUE_NONE, 'Ne pas executer la migration Doctrine')
            ->addOption('no-assets',   null, InputOption::VALUE_NONE, 'Ne pas copier les assets publics')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io          = new SymfonyStyle($input, $output);
        $projectDir  = $this->kernel->getProjectDir();
        $bundleDir   = \dirname(__DIR__, 2); // racine du bundle
        $force       = $input->getOption('force');

        $io->title('Installation du CafeCultureBundle');
        $io->text('Projet : <info>' . $projectDir . '</info>');
        $io->text('Bundle : <info>' . $bundleDir . '</info>');
        $io->newLine();

        $errors = [];

        // ── 1. Fichier de configuration du bundle ─────────────────────────
        $this->step($io, '1/6', 'Configuration du bundle');
        $src = $bundleDir . '/config/packages/cafe_culture.yaml';
        $dst = $projectDir . '/config/packages/cafe_culture.yaml';
        $errors = array_merge($errors, $this->copyFile($io, $src, $dst, $force));

        // ── 2. Fichier de routes ──────────────────────────────────────────
        $this->step($io, '2/6', 'Import des routes');
        $routesDst = $projectDir . '/config/routes/cafe_culture.yaml';
        $routesSrc = $bundleDir . '/config/routes.yaml';

        // Génère un fichier de routes avec préfixe propre plutôt que de copier brut
        $routesContent = <<<YAML
# Routes du CafeCultureBundle
# Generees par : php bin/console cafe:install
cafe_culture_blog:
    resource: '@CafeCultureBlogBundle/src/Controller/BlogController.php'
    type: attribute
    prefix: /blog

cafe_culture_api:
    resource: '@CafeCultureBlogBundle/src/Controller/ApiController.php'
    type: attribute
    prefix: /api/cafe

cafe_culture_sitemap:
    resource: '@CafeCultureBlogBundle/src/Controller/SitemapController.php'
    type: attribute

cafe_culture_admin:
    resource: '@CafeCultureBlogBundle/src/Controller/AdminController.php'
    type: attribute
    prefix: /admin/cafe
YAML;
        $errors = array_merge($errors, $this->writeFile($io, $routesDst, $routesContent, $force));

        // ── 3. Assets publics ─────────────────────────────────────────────
        if (!$input->getOption('no-assets')) {
            $this->step($io, '3/6', 'Copie des assets publics');
            $assetSrc = $bundleDir . '/public/bundles/cafeculturebundle';
            $assetDst = $projectDir . '/public/bundles/cafeculturebundle';
            $errors = array_merge($errors, $this->copyDirectory($io, $assetSrc, $assetDst, $force));
        } else {
            $io->text('  <comment>[SKIP]</comment> Assets (--no-assets)');
        }

        // ── 4. Dossier uploads ────────────────────────────────────────────
        $this->step($io, '4/6', 'Creation du repertoire uploads');
        $uploadsDir = $projectDir . '/public/uploads/cafe-culture';
        if (!$this->fs->exists($uploadsDir)) {
            $this->fs->mkdir($uploadsDir, 0775);
            $this->fs->touch($uploadsDir . '/.gitkeep');
            $io->text('  <info>[OK]</info>  Cree : public/uploads/cafe-culture/');
        } else {
            $io->text('  <comment>[SKIP]</comment> Deja existant : public/uploads/cafe-culture/');
        }

        // ── 5. Migration Doctrine ─────────────────────────────────────────
        if (!$input->getOption('no-migrate')) {
            $this->step($io, '5/6', 'Migration Doctrine (creation des tables)');
            $migrateErrors = $this->runCommand($output, 'doctrine:migrations:migrate', [
                '--no-interaction' => true,
                '--allow-no-migration' => true,
            ]);
            if ($migrateErrors) {
                $errors[] = 'La migration Doctrine a echoue. Verifiez DATABASE_URL dans .env.local';
                $io->text('  <error>[FAIL]</error> Migration echouee - verifiez votre connexion BDD');
            } else {
                $io->text('  <info>[OK]</info>  Migration executee avec succes');
            }
        } else {
            $io->text('  <comment>[SKIP]</comment> Migration (--no-migrate)');
        }

        // ── 6. Fixtures de demonstration ──────────────────────────────────
        $this->step($io, '6/6', 'Fixtures de demonstration');
        if ($input->getOption('fixtures')) {
            $fixtureErrors = $this->runCommand($output, 'doctrine:fixtures:load', [
                '--no-interaction' => true,
                '--append'         => true,
            ]);
            if ($fixtureErrors) {
                $errors[] = 'Le chargement des fixtures a echoue.';
                $io->text('  <error>[FAIL]</error> Fixtures echouees');
            } else {
                $io->text('  <info>[OK]</info>  Fixtures chargees (6 articles, 6 categories, 15 tags)');
            }
        } else {
            $io->text('  <comment>[SKIP]</comment> Fixtures (ajoutez --fixtures pour les charger)');
        }

        // ── Bilan ─────────────────────────────────────────────────────────
        $io->newLine();

        if (!empty($errors)) {
            $io->error('Installation terminee avec des avertissements :');
            foreach ($errors as $err) {
                $io->text('  - ' . $err);
            }
            $io->newLine();
        } else {
            $io->success('Installation reussie !');
        }

        $io->section('Prochaines etapes');
        $io->listing([
            'Verifiez <info>config/packages/cafe_culture.yaml</info> et ajustez les parametres',
            'Assurez-vous que <info>DATABASE_URL</info> est defini dans <info>.env.local</info>',
            'Visitez <info>http://localhost/blog</info> pour voir votre blog',
            'Visitez <info>http://localhost/admin/cafe</info> pour le back-office (ROLE_ADMIN requis)',
            'Personnalisez les templates dans <info>templates/bundles/CafeCultureBlogBundle/</info>',
        ]);

        $io->text('Documentation : <href=https://github.com/cafe-culture/blog-bundle>github.com/cafe-culture/blog-bundle</>');

        return empty($errors) ? Command::SUCCESS : Command::FAILURE;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function step(SymfonyStyle $io, string $num, string $label): void
    {
        $io->text(sprintf('<fg=yellow>[%s]</> %s', $num, $label));
    }

    /**
     * Copie un fichier source -> destination.
     * Retourne un tableau d'erreurs (vide si OK).
     */
    private function copyFile(SymfonyStyle $io, string $src, string $dst, bool $force): array
    {
        if (!$this->fs->exists($src)) {
            $io->text("  <error>[FAIL]</error> Source introuvable : $src");
            return ["Fichier source introuvable : $src"];
        }

        if ($this->fs->exists($dst) && !$force) {
            $io->text("  <comment>[SKIP]</comment> Deja existant : " . $this->relativePath($dst));
            return [];
        }

        $this->fs->copy($src, $dst, $force);
        $io->text("  <info>[OK]</info>  Copie : " . $this->relativePath($dst));
        return [];
    }

    /**
     * Ecrit du contenu dans un fichier.
     */
    private function writeFile(SymfonyStyle $io, string $dst, string $content, bool $force): array
    {
        if ($this->fs->exists($dst) && !$force) {
            $io->text("  <comment>[SKIP]</comment> Deja existant : " . $this->relativePath($dst));
            return [];
        }

        $this->fs->mkdir(\dirname($dst));
        $this->fs->dumpFile($dst, $content);
        $io->text("  <info>[OK]</info>  Ecrit : " . $this->relativePath($dst));
        return [];
    }

    /**
     * Copie un repertoire entier de facon recursive.
     */
    private function copyDirectory(SymfonyStyle $io, string $src, string $dst, bool $force): array
    {
        if (!$this->fs->exists($src)) {
            $io->text("  <error>[FAIL]</error> Repertoire source introuvable : $src");
            return ["Repertoire introuvable : $src"];
        }

        if ($this->fs->exists($dst) && !$force) {
            $io->text("  <comment>[SKIP]</comment> Deja existant : " . $this->relativePath($dst));
            return [];
        }

        $this->fs->mirror($src, $dst, null, ['override' => $force, 'delete' => false]);
        $io->text("  <info>[OK]</info>  Repertoire copie : " . $this->relativePath($dst));
        return [];
    }

    /**
     * Execute une commande Symfony console.
     * Retourne true si erreur.
     */
    private function runCommand(OutputInterface $output, string $command, array $args = []): bool
    {
        try {
            $app    = $this->getApplication();
            $cmd    = $app->find($command);
            $input  = new ArrayInput(array_merge(['command' => $command], $args));
            $input->setInteractive(false);
            $result = $cmd->run($input, $output);
            return $result !== Command::SUCCESS;
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function relativePath(string $path): string
    {
        $base = $this->kernel->getProjectDir() . '/';
        return str_starts_with($path, $base) ? substr($path, \strlen($base)) : $path;
    }
}
