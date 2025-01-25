<?php

namespace App\Command\AiDevs3Tasks;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:S1e3', description: 'Week 1 / task wednesday')]
class S1e3 extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rawData = $this->cache->get('rawData', function () {
            $request = $this->httpClient->request('GET', $this->aiDevs3Endpoint['S1E3_CHECK_DATA']);
            return json_decode($request->getContent());
        });

        foreach ($rawData->{'test-data'} as &$data) {
            $data->answer = eval("return $data->question ;");
            if (isset($data->test)) {
                $gptAnswer = $this->GPTservice->oneShootPrompt(
                    'Odpowiedz krótko na pytanie',
                    $data->test->q
                );
                $data->test->a = $gptAnswer;
            }
        }
        $rawData->apikey = $this->envParma->get('API_KEY_AIDEVS');

        $result = $this->aiDev3PreWorkService->answerToAiDevs('JSON', $rawData);

        dump($result);

        $output->writeln('Success');
        return Command::SUCCESS;
    }
}
