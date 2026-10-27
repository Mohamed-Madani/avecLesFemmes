<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mohamedmadani:db:create',
    description: 'Supprime la base de données si elle existe, puis la recrée',
)]
class DbCreateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1. On supprime la base si elle existe
        $io->section('Suppression de la base de données');
        $drop = $this->getApplication()->find('doctrine:database:drop');
        $drop->run(new ArrayInput([
            '--force' => true,
            '--if-exists' => true,
        ]), $output);

        // 2. On recrée la base
        $io->section('Création de la base de données');
        $create = $this->getApplication()->find('doctrine:database:create');
        $resultat = $create->run(new ArrayInput([]), $output);

        if ($resultat !== Command::SUCCESS) {
            $io->error('La base de données n\'a pas pu être créée.');
            return Command::FAILURE;
        }

        $io->success('La base de données a été recréée.');
        return Command::SUCCESS;
    }
}
