<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mohamedmadani:cache:clear',
    description: 'Vide le cache de l\'application',
)]
class CacheClearCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // On appelle la commande native cache:clear
        $commande = $this->getApplication()->find('cache:clear');
        $resultat = $commande->run(new ArrayInput([]), $output);

        if ($resultat !== Command::SUCCESS) {
            $io->error('Le cache n\'a pas pu être vidé.');
            return Command::FAILURE;
        }

        $io->success('Le cache a été vidé.');
        return Command::SUCCESS;
    }
}
