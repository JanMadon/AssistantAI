<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class HelloWorldCommand extends Command
{
// Ustawienie nazwy komendy
    public function __construct()
    {
        parent::__construct('app:hello-world');
    }

    protected function configure(): void
    {
        $this->setDescription('Przykładowa komenda "Hello World".')
            ->setHelp('Ta komenda wyświetla komunikat "Hello World".');
        $this->addArgument('name', InputArgument::OPTIONAL, 'Imię osoby, którą chcesz przywitać'); // if argument add
        $this->addOption('surname', null, InputOption::VALUE_OPTIONAL, 'nazwisko');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nameArg = $input->getArgument('name');
        $surnameFlag = $input->getOption('surname');


        // this console return
        $output->writeln(
            'Hello World!' . PHP_EOL
            . "argument: $nameArg" . PHP_EOL
            . "option: $surnameFlag" . PHP_EOL
        );

    // Zwróć status powodzenia (0)
        return Command::SUCCESS;
    }
}
