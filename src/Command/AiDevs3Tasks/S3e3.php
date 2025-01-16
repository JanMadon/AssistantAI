<?php

namespace App\Command\AiDevs3Tasks;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use function PHPUnit\Framework\isNull;

#[AsCommand(name: 'app:S3e3', description: 'Week 3 / task wednesday')]
class S3e3 extends BaseCommand
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

        $prompt = file_get_contents('Prompts/AiDev3/S3E3/prompt.txt');

        $messages = [
            [
                'role' => 'system',
                'content' => $prompt,
            ],
            [
                'role' => 'user',
                'content' => 'START',
            ]

        ];

        $continue = true;
        if(!$helper->ask($input, $output, new ConfirmationQuestion('start conversation? (y/n)'))) {
            $continue = false;
        }
        while ($continue) {
            print_r('conversation');
            dump($messages);

            $answerGpt = $this->GPTservice->simplePrompt($messages);
            dump('GPT answer:' . $answerGpt);

            if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to add gpt answer to conversation? (y/n)'))) {
                $message = [
                    'role' => 'assistant',
                    'content' => $answerGpt,
                ];
                $messages[] = $message;
            }

            $database_data = $this->getDatabaseResult($answerGpt);
            dump($database_data);
            if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to add database-data to conversation? (y/n)'))) {
                $message = [
                    'role' => 'user',
                    'content' => json_encode($database_data->reply),
                ];
                $messages[] = $message;
            }


            if (!$helper->ask($input, $output, new ConfirmationQuestion('Do you want to proceed? (y/n)'))) {
                $continue = false;
            }
            print_r('---------------------------------------------------------------------------------------');
        }
        $result = ['4278','9294'];
        $responseAIdevs =  $this->aiDev3PreWorkService->answerToAiDevs(
            'database',
            $result,
            $this->aiDevs3Endpoint['REPORT_URL']
        );
        dd($responseAIdevs);

        return Command::SUCCESS;
    }

    private function getDatabaseResult($query)
    {
        $payload = [
            "task" => "database",
            "apikey" => $this->envParma->get('API_KEY_AIDEVS'),
            "query" => "$query"
        ];

        $response = $this->httpClient->request(
            'POST',
            $this->aiDevs3Endpoint['S3E3_API_DATABASE'],
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload
            ]
        );

        $response = $response->getContent(false);

        return json_decode($response);
    }


}
