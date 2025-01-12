<?php

namespace App\Command\AiDevs3Tasks;

use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use function PHPUnit\Framework\isNull;

#[AsCommand(name: 'app:S5e4', description: 'Add a short description for your command')]
class S5e4 extends BaseCommand
{

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // start task send address my application
        $response = $this->aiDev3PreWorkService->answerToAiDevs(
            'serce',
            'https://d5c9-2a02-a31b-21c3-9080-78ee-e273-94ca-d0c6.ngrok-free.app/s4e4' // i use ngrok (like s4e4)
        );

        dump($response);


        return Command::SUCCESS;
    }
}
