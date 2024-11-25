<?php

namespace App\Command\AiDevs3Tasks;

use mysql_xdevapi\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(name: 'app:S2e5Command',description: 'Add a short description for your command')]
class S2e5Command extends BaseCommand
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

        try {
            $response = $this->httpClient->request(
                'GET',
                $this->aiDevs3Endpoint['S2E5_HTML_DATA']
            );
            $domHtml = $response->getContent(false);
        } catch (\Throwable $e) {
            print_r($e->getMessage());
            return Command::FAILURE;
        }

        $crawler = new Crawler($domHtml);
        $mainContainerHtml = $crawler->filter('div.container')->html();

        $paragraphs = $crawler->filter('p')->each(function (Crawler $node, $i) {
            return $node->text();
        });

        $images = $crawler->filter('img')->each(function (Crawler $node, $i) {
            return $node->attr('src');
        });

        $mp3_records_urls = $crawler->filter('audio')->each(function (Crawler $node, $i) {
            return $node->filter('source')->attr('src');
        });

        $mp3_records_full_urls = array_map(function ($record) {
            return $this->aiDevs3Endpoint['S2E5_HTML_DATA'].'/'. $record;
        }, $mp3_records_urls);

        if(!file_exists('var/AiDev3_data/audioS2E5/transcription.txt')){
            foreach ($mp3_records_full_urls as $record_url) {
                $record = file_get_contents(str_replace('/arxiv-draft.html', '', $record_url));
                $result = file_put_contents(
                    $savePath = 'var/AiDev3_data/audioS2E5/'. explode('/', $mp3_records_urls[0])[1],
                    $record
                );
            }
            $transcription = $this->GPTservice->makeTranscription($savePath);
            // REMEMBER: if use file_put_contents, you need use exist patch (exist directory)
            file_put_contents('var/AiDev3_data/audioS2E5/transcription.txt', $transcription);
        }

        /** add transcription bootom mp3 player */
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($mainContainerHtml);
        dd($domDocument);


        $content = $crawler->filter('div.container')->html();
        dd($mp3_records_full_urls);


       print_r($response->getContent());



        return Command::SUCCESS;
    }
}
