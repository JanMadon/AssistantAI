<?php

namespace App\Command\AiDevs3Tasks;

use http\Env\Response;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use function PHPUnit\Framework\isNull;

#[AsCommand(name: 'app:S3e4', description: 'Add a short description for your command')]
class S3e4 extends BaseCommand
{

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $prompt = file_get_contents('Prompts/AiDev3/S3E4/prompt.txt');
        $startData = file_get_contents($this->aiDevs3Endpoint['S3E4_DATA_BARBARA']);


        $messages = [
            ['role' => 'system', 'content' => $prompt],
            ['role' => 'user', 'content' => $startData]
        ];


        $answerGpt = $this->GPTservice->simplePrompt($messages, 'gpt-4o');
        $people = ['BARBARA', 'ALEKSANDER', 'ANDRZEJ', 'RAFAL']; //json_decode($answerGpt)->people;
        $places = ['KRAKOW', 'WARSZAWA'];//json_decode($answerGpt)->places;

        $askedPlaces = [];

        while (true) {
            $people_data = [];
            foreach ($people as $person) {
                $person_data = $this->getInfo('people', $person)->message;
                $person_data = $person_data == '[**RESTRICTED DATA**]' ? 'null' : $person_data;
                $people_data [$person] = explode(' ', $person_data); //explode(' ', $person_data->message);
            }


            $places_data = [];
            foreach ($places as $place) {
                $place_data = $this->getInfo('places', $place)->message;
                $place_data = $place_data == '[**RESTRICTED DATA**]' ? 'null' : $place_data;
                $places_data[$place] = explode(' ', $place_data); // explode(' ', $place_data->message);
            }

            foreach ($this->array2dValues($people_data) as $place) {
                print_r('-----place: '. $place . ' ----------' . PHP_EOL);
                if(in_array($place, $askedPlaces)) {
                    continue;
                }
                $askedPlaces[] = $place;
                if (!$helper->ask($input, $output, new ConfirmationQuestion('Do you want to ask? (y/n)'))) {
                    continue;
                }
                $responseAIdevs = $this->aiDev3PreWorkService->answerToAiDevs(
                    'loop',
                    $place,
                    $this->aiDevs3Endpoint['REPORT_URL']
                );
                dump($responseAIdevs);
            }

            if (!$helper->ask($input, $output, new ConfirmationQuestion('Do you want to proceed? (y/n)'))) {
                $continue = false;
                break;
            }

            $people = array_diff(array_diff($this->array2dValues($places_data), $people));
            $places = array_diff(array_diff($this->array2dValues($people_data), $places));
        }

        dump($places_data);
        dd($this->array2dValues($places_data));

        return Command::SUCCESS;
    }

    private function getInfo(string $type, string $item)
    {
        if ($type == 'places') {
            $endpoint = $this->aiDevs3Endpoint['S3E4_PLACES'];
        } elseif ($type === 'people') {
            $endpoint = $this->aiDevs3Endpoint['S3E4_PEOPLE'];
        } else {
            return false;
        }

        $payload = [
            'apikey' => $this->envParma->get('API_KEY_AIDEVS'),
            'query' => $item,
        ];

        $request = $this->httpClient->request(
            'POST',
            $endpoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'json' => $payload
            ]
        );

        $response = $request->getContent(false);

        return json_decode($response);
    }

    private function array2dValues(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($value[0] == 'null') {
                continue;
            }
            $result = array_merge($result, $value);
        }
        return array_unique($result);
    }
}
