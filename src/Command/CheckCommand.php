<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Verifie que l'installation du bundle est complete et coherente.
 *
 * Usage : php bin/console cafe:check
 */
#[AsCommand(
    name: 'cafe:check',
    description: 'Verifie que le CafeCultureBundle est correctement installe et configure.'
)]
class CheckCommand extends Command
{
    public function __construct(
        private readonly KernelInterface       $kernel,
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface       $router,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io         = new SymfonyStyle($input, $output);
        $projectDir = $this->kernel->getProjectDir();
        $fs         = new Filesystem();
        $pass       = 0;
        $fail       = 0;
        $warn       = 0;

        $io->title('Verification de l\'installation — CafeCultureBundle');

        $check = function (string $label, bool $ok, string $okMsg = '', string $failMsg = '', bool $warning = false) use ($io, &$pass, &$fail, &$warn) {
            if ($ok) {
                $io->text(sprintf('  <info>[OK]</info>  %s%s', $label, $okMsg ? " — $okMsg" : ''));
                ++$pass;
            } elseif ($warning) {
                $io->text(sprintf('  <comment>[WARN]</comment> %s%s', $label, $failMsg ? " — $failMsg" : ''));
                ++$warn;
            } else {
                $io->text(sprintf('  <e>[FAIL]</e> %s%s', $label, $failMsg ? " — $failMsg" : ''));
                ++$fail;
            }
        };

        // ── Fichiers de configuration ──────────────────────────────────────
        $io->section('Fichiers de configuration');

        $check(
            'config/packages/cafe_culture.yaml',
            $fs->exists($projectDir . '/config/packages/cafe_culture.yaml'),
            '',
            'Manquant — executez : php bin/console cafe:install'
        );
        $check(
            'config/routes/cafe_culture.yaml',
            $fs->exists($projectDir . '/config/routes/cafe_culture.yaml'),
            '',
            'Manquant — executez : php bin/console cafe:install'
        );

        // ── Assets publics ─────────────────────────────────────────────────
        $io->section('Assets publics');

        $assets = [
            'public/bundles/cafeculturebundle/css/main.css',
            'public/bundles/cafeculturebundle/css/admin.css',
            'public/bundles/cafeculturebundle/js/app.js',
            'public/bundles/cafeculturebundle/js/admin.js',
        ];
        foreach ($assets as $asset) {
            $check($asset, $fs->exists($projectDir . '/' . $asset), '', 'Manquant — executez : php bin/console cafe:install');
        }

        $check(
            'public/uploads/cafe-culture/',
            $fs->exists($projectDir . '/public/uploads/cafe-culture'),
            '',
            'Dossier d\'upload manquant',
            true
        );

        // ── Base de donnees ────────────────────────────────────────────────
        $io->section('Base de donnees');

        $tables = ['cafe_article', 'cafe_category', 'cafe_tag', 'cafe_comment', 'cafe_rating'];
        foreach ($tables as $table) {
            try {
                $conn   = $this->em->getConnection();
                $exists = $conn->createSchemaManager()->tablesExist([$table]);
                $check("Table $table", $exists, '', 'Table absente — executez : php bin/console doctrine:migrations:migrate');
            } catch (\Throwable) {
                $check("Table $table", false, '', 'Impossible de verifier (connexion BDD ?)');
            }
        }

        // ── Routes ────────────────────────────────────────────────────────
        $io->section('Routes');

        $requiredRoutes = [
            'cafe_culture_index',
            'cafe_culture_article_show',
            'cafe_culture_category',
            'cafe_culture_admin_dashboard',
            'cafe_culture_sitemap',
            'cafe_culture_rss',
        ];
        foreach ($requiredRoutes as $route) {
            try {
                $this->router->generate($route, $route === 'cafe_culture_article_show' ? ['slug' => 'test'] : []);
                $check("Route $route", true);
            } catch (\Throwable) {
                $check("Route $route", false, '', 'Route non trouvee — verifiez config/routes/cafe_culture.yaml');
            }
        }

        // ── Variables d'environnement ──────────────────────────────────────
        $io->section('Variables d\'environnement');

        $check(
            'DATABASE_URL',
            !empty($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL')),
            '',
            'Non definie — ajoutez-la dans .env.local',
            true
        );

        // ── Bilan ──────────────────────────────────────────────────────────
        $io->newLine();
        $io->text(sprintf(
            'Bilan : <info>%d OK</info>, <comment>%d avertissement(s)</comment>, <e>%d echec(s)</e>',
            $pass, $warn, $fail
        ));
        $io->newLine();

        if ($fail > 0) {
            $io->error('L\'installation est incomplete. Executez : php bin/console cafe:install');
            return Command::FAILURE;
        }

        if ($warn > 0) {
            $io->warning('Installation fonctionnelle avec quelques avertissements.');
            return Command::SUCCESS;
        }

        $io->success('Installation complete et operationnelle !');
        return Command::SUCCESS;
    }
}
