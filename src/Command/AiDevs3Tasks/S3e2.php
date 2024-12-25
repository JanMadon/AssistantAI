<?php

namespace App\Command\AiDevs3Tasks;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function PHPUnit\Framework\isNull;

#[AsCommand(name: 'app:S3e2', description: 'Add a short description for your command')]
class S3e2 extends BaseCommand
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
        $test = '["broń", "Mikroskalowy Wyzwalacz Plazmowy", "plazma", "temperatura", "zdolność", "nieskuteczność", "dystans", "system", "stabilizacja", "prototyp"]';

        //dd(json_decode($test));


        dump($this->createCollection('do-not-share', 1536));

        $path_datas = glob('var/AIDevs/do-not-share' . '/*.txt',);

        $datas = [];
        foreach ($path_datas as $path_data) {
            $datas[basename($path_data, '.txt')] = file_get_contents($path_data);
        }

        $id = 1;
        $vectorRecords = [];
        foreach ($datas as $fileName => $fileContent) {
            
            //generate keywords
            $prompt = [
                ['role' => 'system', 'content' => file_get_contents('Prompts/AiDev3/S3E2/keyWords.txt')],
                ['role' => 'user', 'content' => $fileContent]
            ];
            $keywords_json = $this->GPTservice->simplePrompt($prompt);
            dump($keywords_json);
            $keywords_json = str_replace(['```','json'], '', $keywords_json);
            $keywords = json_decode($keywords_json);
            if(is_null($keywords)){
                dump('error - decode keywords');
                dd($keywords);
            };

            //generate embeding
            $embeding = $this->GPTservice->makeEmbeding($fileContent);
            if(count($embeding) != 1536){
                dump('error with embeding response');
                dd($embeding);
            };

            // prepare record 
            $vectorRecords[] = [
                'id' => $id,
                'vector' => $embeding,
                'payload' => ['date' => $fileName, 'keywords' => $keywords]
            ];

            dump('vectors ' . $id++ . ' created');
        }

        $seveResult = $this->addVectors($vectorRecords, 'do-not-share');
        dump($seveResult);

        return Command::SUCCESS;
    }

    private function createCollection(string $colectionName, int $vectorSize)
    {
        $payload = [
            'vectors' => [
                'size' => $vectorSize,
                'distance' => 'Dot'
            ]
        ];

        $response = $this->httpClient->request(
            'PUT',
            'http://localhost:6333/collections/' . $colectionName,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload
            ]
        );
        $jsonResponse = $response->getContent(false);

        return json_decode($jsonResponse)->status;
    }

    //vactors = [['id' => {id}, 'vector' => [0.05, 0.76, 0.74], 'payload' => {structure}]...,
    private function addVectors(array $vectors, $colectionName) // todo added colection cto
    {

        $response = $this->httpClient->request(
            'PUT',
            "http://localhost:6333/collections/$colectionName/points",
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $vectors
            ]
        );
        $jsonResponse = $response->getContent(false);

        return json_decode($jsonResponse)->status;
    }
}
