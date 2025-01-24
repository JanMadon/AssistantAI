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

#[AsCommand(name: 'app:S3e5', description: 'Week 3 / task friday')]
class S3e5 extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        $users = $this->getApiDatabase('select * from users');
//        foreach ($users as $user) {
//            $query = "CREATE (:User {id: '$user->id', name: '$user->username'});" . PHP_EOL;
//            file_put_contents('var/AiDev3_data/S3E5/create_users_nodes.txt', $query, FILE_APPEND);
//        }
//
//        $connections = $this->getApiDatabase('select * from connections');
//        foreach ($connections as $connection) {
//            $query = "MATCH (a:User {id: '$connection->user1_id'}), (b:User {id: '$connection->user2_id'})
//                    CREATE (a)-[:ZNA_JAK]->(b);" . PHP_EOL;
//
//
//            file_put_contents('var/AiDev3_data/S3E5/create_relationships.txt', $query, FILE_APPEND);
//        }

        //filled the graph database and ask:  MATCH p = shortestPath((a:User {name: 'Rafał'})-[*]-(b:User {name: 'Barbara'})) RETURN p;
        $result = 'Rafał,Azazel,Aleksander,Barbara';

        $responseAIdevs =  $this->aiDev3PreWorkService->answerToAiDevs(
            'connections',
            $result,
            $this->aiDevs3Endpoint['REPORT_URL']
        );
        dump($responseAIdevs);

        return Command::SUCCESS;
    }

    private function getApiDatabase($query)
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
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'json' => $payload
            ]
        );

        $response = $response->getContent(false);

        return json_decode($response)->reply;
    }


}
