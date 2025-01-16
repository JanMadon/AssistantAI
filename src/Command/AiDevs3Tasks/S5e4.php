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

#[AsCommand(name: 'app:S5e4', description: 'Week 4 / task thursday')]
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
            'https://2ffd-2a02-a31b-21c3-9080-488d-a06d-bff8-7235.ngrok-free.app/s5e4-jb' // I use ngrok (like s4e4)
        );

        dump($response);


        return Command::SUCCESS;
    }
}
