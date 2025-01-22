<?php

namespace App\Command\AiDevs3Tasks;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:S1e2', description: 'Week 1 / task tuesday')]
class S1e2 extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startResponse = $this->requestToAuthTask('READY', 0);
        $msgID = $startResponse->msgID ?? 0;

        $prompt = file_get_contents('Prompts/AiDev3/S1E2/prompt.txt');
        $answerChat = $this->GPTservice->prompt($prompt, $startResponse->text);

        $finishResponse = $this->requestToAuthTask($answerChat, $msgID);

        dump($finishResponse);

        return Command::SUCCESS;
    }

    private function requestToAuthTask(string $text, int $msgId)
    {
        $response = $this->httpClient->request(
            'POST',
            $this->aiDevs3Endpoint['S1E2_AUTH'], [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => ["text" => "$text","msgID" => $msgId]
            ]
        );
        return json_decode($response->getContent(false));
    }




}
