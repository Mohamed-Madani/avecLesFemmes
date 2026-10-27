<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

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

        // 2. On exécute les migrations sans poser de question.
        // On lance un nouveau processus, sinon Doctrine ne voit pas
        // le fichier de migration qui vient d'être créé.
        $io->section('Exécution de la migration');
        $process = new Process([
            PHP_BINARY,
            'bin/console',
            'doctrine:migrations:migrate',
            '--no-interaction',
            '--allow-no-migration',
        ]);
        $process->setTimeout(null);
        $process->run(function ($type, $texte) use ($output) {
            $output->write($texte);
        });

        if (!$process->isSuccessful()) {
            $io->error('La migration a échoué.');
            return Command::FAILURE;
        }

        $io->success('La base de données est à jour.');
        return Command::SUCCESS;
    }
}
