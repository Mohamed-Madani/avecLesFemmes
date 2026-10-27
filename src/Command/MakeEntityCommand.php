<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mohamedmadani:make:entity',
    description: 'Crée ou modifie une entité (appelle make:entity)',
)]
class MakeEntityCommand extends Command
{
    protected function configure(): void
    {
        // Le nom de l'entité est optionnel : s'il n'est pas donné, le maker le demande
        $this->addArgument('nom', InputArgument::OPTIONAL, 'Nom de l\'entité (ex. Campagne)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = [];
        if ($input->getArgument('nom')) {
            $arguments['name'] = $input->getArgument('nom');
        }

        $nouvelleInput = new ArrayInput($arguments);
        // On garde le mode interactif pour pouvoir répondre aux questions du maker
        $nouvelleInput->setInteractive($input->isInteractive());

        $commande = $this->getApplication()->find('make:entity');

        return $commande->run($nouvelleInput, $output);
    }
}
