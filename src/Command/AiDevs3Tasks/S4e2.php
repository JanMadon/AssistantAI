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

#[AsCommand(name: 'app:S4e2', description: 'Week 4 / task tuesday')]
class S4e2 extends BaseCommand
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

        $helper = $this->getHelper('question');

        if($helper->ask($input, $output, new ConfirmationQuestion('Do you want create fine-tining file? (y/n)'))) {
            $correctData = file_get_contents('var/AiDev3_data/S4E2/correct.txt');
            $arrayCorrectData = explode("\n", $correctData);
            $this->createRow($arrayCorrectData, 'correct');

            $incorrectData = file_get_contents('var/AiDev3_data/S4E2/incorrect.txt');
            $arrayIncorrectData = explode("\n", $incorrectData);
            $this->createRow($arrayIncorrectData, 'incorrect');
        }

        $verify = file_get_contents('var/AiDev3_data/S4E2/verify.txt');
        $arrayVerify = explode("\n", $verify);

        $verified = [];
        foreach($arrayVerify as $verifyRow) {
            $data = explode('=', $verifyRow);

            $messages = [
                ['role' => 'system','content' => 'Sprawdź poprawność danych.'],
                ['role' => 'user','content' => $data[1]]
            ];
            $answerGpt = $this->GPTservice->simplePrompt($messages, 'ft:gpt-4o-mini-2024-07-18:personal:ai-dev3-data-s4e2:Ali37FZ0');
            dump($answerGpt);

            if($answerGpt === 'correct') {
                $verified[] = $data[0];
            }
        }
        dump($verified);
        $answer = $this->aiDev3PreWorkService->answerToAiDevs('research', $verified);

        dump($answer);

        return Command::SUCCESS;
    }

    private function createRow(array $rows, string $type)
    {
        foreach ($rows as $key => $row) {
            $json = '{"messages": [{"role": "system", "content": "Sprawdź poprawność danych."}, {"role": "user", "content": "'.$row.'"}, {"role": "assistant", "content": "'.$type.'"}]}';
            file_put_contents('var/AiDev3_data/S4E2/data.jsonl', $json.PHP_EOL, FILE_APPEND);
        }
    }
}
