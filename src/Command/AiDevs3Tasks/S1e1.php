<?php

namespace App\Command\AiDevs3Tasks;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:S1e1', description: 'Week 1 / task monday')]
class S1e1 extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $htmlDomContent = file_get_contents($this->aiDevs3Endpoint['S1E1_LOGIN']);
        $dom = new DOMDocument();
        $dom->loadHTML($htmlDomContent);
        $question = substr($dom->getElementById('human-question')->textContent, 9);

        $answerGPT = $this->GPTservice->oneShootPrompt('Podaj rok jako cyfrę nic więcej nie zwracaj', $question, 'gpt-4o-mini');

        $responseAiDevs = $this->httpClient->request(
            'POST',
            $this->AiDevs3Endpoint['S1E1_LOGIN'], [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => [
                    'username' => 'tester',
                    'password' => '574e112a',
                    'answer' => (int)$answerGPT,
                ]
            ]
        );

        dump($responseAiDevs);

        return Command::SUCCESS;
    }

}
