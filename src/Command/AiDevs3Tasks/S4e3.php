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

#[AsCommand(name: 'app:S4e3', description: 'Week 4 / task wednesday')]
class S4e3 extends BaseCommand
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

        $prompt = file_get_contents('Prompts/AiDev3/S4E3/prompt.txt');
        $domHtml = $this->getPageContent($this->aiDevs3Endpoint['S4E3_CONTENT_PAGE']);
        $questions = json_decode($this->getPageContent($this->aiDevs3Endpoint['S4E3_QUESTION']), true);
        $questions = array_values($questions);
        $questions = [];

        $answers = [];
        foreach ($questions as $question) {

            $messages = [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $question],
                ['role' => 'user', 'content' => $domHtml]
            ];

            do {
                dump($messages);
                if (!$helper->ask($input, $output, new ConfirmationQuestion('Do you want to continue? (y/n)'))) {
                    break;
                }

                $answerGPT = $this->GPTservice->simplePrompt($messages, 'gpt-4o');
                dump($answerGPT);
                $nextPageUrlOrAnswer = filter_var($answerGPT, FILTER_VALIDATE_URL);
                dump($nextPageUrlOrAnswer);

                if ($helper->ask($input, $output, new ConfirmationQuestion('Do you want to add url? (y/n)'))) {
                    $url = $this->aiDevs3Endpoint['S4E3_CONTENT_PAGE'] . $answerGPT;
                    $messages[] = ['role' => 'user', 'content' => $this->getPageContent($url)];
                } else {
                    $answers[] = $answerGPT;
                    dump('CORRECT ANSWER');
                    break;

                }

            } while (true);
        }

        $answers = [
            'kontakt@softoai.whatever',
            'https://banan.ag3nts.org/',
            'ISO 9001 oraz ISO/IEC 27001'
        ];
        $i = 1;
        foreach ($answers as $answer) {
            $answerPayload['0'.$i] = $answer;
            $i++;
        }
        dump($answerPayload);

        $answerToAiDevs = $this->aiDev3PreWorkService->answerToAiDevs('softo', $answerPayload);
        dump($answerToAiDevs);

        return Command::SUCCESS;
    }

    private function getPageContent(string $url): string
    {
        $response = $this->httpClient->request(
            'GET',
            $url
        );
        $domHtml = $response->getContent(false);
        return $domHtml;
    }

}
