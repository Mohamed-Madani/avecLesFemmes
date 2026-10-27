<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mohamedmadani:migration',
    description: 'Génère la migration puis l\'exécute',
)]
class MigrationCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1. On génère le fichier de migration (comme make:migration)
        $io->section('Génération de la migration');
        $makeMigration = $this->getApplication()->find('make:migration');
        $makeMigration->run(new ArrayInput([]), $output);

        // 2. On exécute les migrations sans poser de question
        $io->section('Exécution de la migration');
        $migrate = $this->getApplication()->find('doctrine:migrations:migrate');
        $migrateInput = new ArrayInput([
            '--allow-no-migration' => true,
        ]);
        $migrateInput->setInteractive(false);
        $resultat = $migrate->run($migrateInput, $output);

        if ($resultat !== Command::SUCCESS) {
            $io->error('La migration a échoué.');
            return Command::FAILURE;
        }

        $io->success('La base de données est à jour.');
        return Command::SUCCESS;
    }
}
