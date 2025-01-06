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
use function Symfony\Component\Translation\t;

#[AsCommand(name: 'app:S5e2', description: 'Add a short description for your command')]
class S5e2 extends BaseCommand
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
        //$question = file_get_contents($this->aiDevs3Endpoint['S5E2_LOGS_QUESTION']);
        //$question = json_decode($question);

        $names = ['Rafał', 'Azazel', 'Samuel']; //$this->getInfo('Lubawa')->message;
//dd($names);
        $payload = [];
        foreach ($names as $name) {
            $dataBase = $this->getDatabaseResult('select * from users where username = "'.$name.'";')->reply;
            $userId = $dataBase[0]->id;
            $coordinate = $this->getCoordinate($userId)->message;
            $payload[$name] = [
                'lat' => $coordinate->lat,
                'lon' => $coordinate->lon,
            ];
        }
        dump($payload);

        $answerToAiDevs = $this->aiDev3PreWorkService->answerToAiDevs('gps', $payload);
        dump($answerToAiDevs);

        return Command::SUCCESS;
    }

    private function getCoordinate($userId)
    {
        $payload = ["userID" => $userId];

        $response = $this->httpClient->request(
            'POST',
            $this->aiDevs3Endpoint['S5E2_GPS'],
            [
                'headers' => ['content-type' => 'application/json'],
                'json' => $payload
            ]
        );
        $response = $response->getContent(false);
        return json_decode($response);
    }

    private function getInfo(string $place)
    {
        $payload = [
            'apikey' => $this->envParma->get('API_KEY_AIDEVS'),
            'query' => $place,
        ];

        $request = $this->httpClient->request(
            'POST',
            $this->aiDevs3Endpoint['S3E4_PLACES'],
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'json' => $payload
            ]
        );

        $response = $request->getContent(false);

        return json_decode($response);
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

    private function makeRequest()
    {
        $payload = ['password' => 'NONOMNISMORIAR'];

        $response = $this->httpClient->request(
            'POST',
            'https://rafal.ag3nts.org/b46c3',
            [
                'headers' => ['content-type' => 'application/json'],
                'json' => $payload
            ]
        );
        return json_decode($response->getContent(false));
        //dd($response->getContent(false));
    }
}
