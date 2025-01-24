<?php

namespace App\Command\AiDevs3Tasks;

use mysql_xdevapi\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(name: 'app:S1e5',description: 'Week 2 / task friday')]
class S1e5 extends BaseCommand
{
    /**
     * @param array $mp3_records_full_urls
     * @param $mp3_records_urls
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sensitiveData = file_get_contents($this->aiDevs3Endpoint['S1E5_PERSONAL_DATA']);
        $output->writeln('Sensitive  data: '. dump($sensitiveData));
        $output->writeln('------------------------------------');

        $systemPrompt = 'Zamień wszelkie wrażliwe dane (imię + nazwisko, nazwę ulicy + numer, miasto, wiek osoby na słowo CENZURA';

        $gptResponse = $this->GPTservice->prompt($systemPrompt, $sensitiveData, 'gpt-4');
        $output->writeln('Chat response: '. dump($gptResponse));
        $output->writeln('------------------------------------');

        $aiDevsResponse=$this->aiDev3PreWorkService->answerToAiDevs('CENZURA', $gptResponse);
        $output->writeln('AIDevs response: ');
        dump($aiDevsResponse);

        return Command::SUCCESS;
    }
}
