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
use Symfony\Contracts\HttpClient\ResponseInterface;
use function PHPUnit\Framework\isNull;
use function Symfony\Component\Translation\t;

#[AsCommand(name: 'app:S5e3', description: 'Week 5 / task wednesday')]
class S5e3 extends BaseCommand
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
        /*  hint
            $content = file_get_contents('var/AiDev3_data/S5E3/podpowiedz_base64.txt');
            file_put_contents('var/AiDev3_data/S5E3/podpowiedz.txt', base64_decode($content));
        */
        $helper = $this->getHelper('question');
        if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to get and scalp text? (y/n)'))) {
            $domHtml = file_get_contents('https://centrala.ag3nts.org/dane/arxiv-draft.html');
            $plainText = strip_tags($domHtml);
            file_put_contents('var/AiDev3_data/S5E3/data.txt', $plainText);

            echo $plainText;

            return Command::SUCCESS;
        }

        $startTime = microtime(true);

        $token = $this->makeRequest()->message;
        print_r('token:'. $token . PHP_EOL);
        $login = $this->makeRequest($token);
        dump($login);


        // To make async requests you need to have the php-curl extension enabled
        $promise1 = $this->httpClient->request('GET', $login->message->challenges[0]);
        $promise2 = $this->httpClient->request('GET', $login->message->challenges[1]);

        $content = [];
        foreach ([$promise1, $promise2] as $promise) {
            /** @var ResponseInterface $promise */
            $result = $promise->getContent(false); // Czeka na zakończenie tego konkretnego żądania
            $content[] = json_decode($result);
        }
        dump($content);

        $answers = [];
        foreach ($content as $item) {

            $promise = $this->prepareRequest($item);
            $resultJson = $promise->getContent(false);
            $result = json_decode($resultJson);
            if(!isset($result->choices[0]->message->content)){
                dd($result);
            }
            $response = json_decode($result->choices[0]->message->content);
            foreach ($response->answers as $answer) {
                $answers[] = $answer;
            }
        }

        dump( $answers);


        $data = [
            'apikey' => $this->envParma->get('API_KEY_AIDEVS'),
            'timestamp' => $login->message->timestamp,
            'signature' => $login->message->signature,
            'answer' => $answers
        ];

        $response = $this->httpClient->request(
            'POST',
            $this->aiDevs3Endpoint['S5E3_RAFAL_URL'],
            [
                'headers' => ['content-type' => 'application/json'],
                'json' => $data
            ]
        );

        dump($response->getContent(false));


        $output->writeln('Execution time: ' . microtime(true) - $startTime . ' seconds');
        return Command::SUCCESS;
    }

    private function prepareRequest($task): ResponseInterface
    {
        if(count($task->data) == 4) {
            $content = 'Odpowiedz na pytania. Najkrócej jak to możliwe!!!
             tipy najstarszy hymn polski to: Bogurodzica
             ###
             format odpowiedzi w json: '
                . json_encode(['answer_1', 'answer_2', 'answer_3', 'answer_4'])
                . ' ### questions: [' . implode(', ', $task->data) . ']'
            ;
        } else {

            $content = 'Na podstawie kontentu odpowiedz na pytania. Najkrócej jak to możliwe!!! format odpowiedzi w json: '
                . json_encode(['answer_1', 'answer_2'])
                . ' ### questions: [' . implode(', ', $task->data) . ']' . PHP_EOL
                . file_get_contents('var/AiDev3_data/S5E3/data.txt')
            ;
        }

        $payload = [
            'model' => 'gpt-4o-mini',
            'response_format'=> ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $content
                ],
            ],
        ];

        $request = $this->httpClient->request(
            'POST',
            'https://api.openai.com/v1/chat/completions',
            [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Content-Length: ' . strlen(json_encode($payload)),
                    'Authorization' => 'Bearer ' . $this->envParma->get('API_KEY_OPENAI')
                ]
            ]
        );
       return $request;
    }

    private function makeRequest($token = null)
    {
        if (is_null($token)) {
            $payload = ['password' => 'NONOMNISMORIAR'];
        } else {
            $payload = ['sign' => $token];
        }

        $response = $this->httpClient->request(
            'POST',
            $this->aiDevs3Endpoint['S5E3_RAFAL_URL'],
            [
                'headers' => ['content-type' => 'application/json'],
                'json' => $payload
            ]
        );
        return json_decode($response->getContent(false));
    }


}
