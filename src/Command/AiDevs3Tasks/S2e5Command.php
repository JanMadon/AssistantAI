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

#[AsCommand(name: 'app:S2e5Command',description: 'Add a short description for your command')]
class S2e5Command extends BaseCommand
{

    /**
     * @param array $mp3_records_full_urls
     * @param $mp3_records_urls
     * @return mixed
     */
    private function saveAndGetTranscriptions(array $mp3_records_full_urls, $mp3_records_urls): mixed
    {
        if (!file_exists('var/AiDev3_data/audioS2E5/transcription.txt')) {
            foreach ($mp3_records_full_urls as $record_url) {
                $record = file_get_contents(str_replace('/arxiv-draft.html', '', $record_url));
                $result = file_put_contents(
                    $savePath = 'var/AiDev3_data/audioS2E5/' . explode('/', $mp3_records_urls)[1],
                    $record
                );
            }
            $transcription = $this->GPTservice->makeTranscription($savePath);
            // REMEMBER: if use file_put_contents, you need use exist patch (exist directory)
            file_put_contents('var/AiDev3_data/audioS2E5/transcription.txt', $transcription);
        }

        return json_decode(file_get_contents('var/AiDev3_data/audioS2E5/transcription.txt'))->text ?? '';

    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$this->test();

        $domHtml = $this->cache->get('domHtml', function() use ($input, $output) {
            $output->writeln('pobrano kontent');
            return $this->getHtmlContent();
        });
        $crawler = new Crawler($domHtml);

        //images
        $images_src = $crawler->filter('figure')->each(function (Crawler $node, $i) {
            return  $node->filter('img')->attr('src');
        });

        //dd($images_src);

        $images = array_map(function ($image) {
            return 'https://centrala.ag3nts.org/dane/'.$image;
        }, $images_src);

        //audios
        $mp3_records_urls = $crawler->filter('audio')->each(function (Crawler $node, $i) {
            return $node->filter('source')->attr('src');
        });

        $mp3_records_full_urls = array_map(function ($record) {
            return $this->aiDevs3Endpoint['S2E5_HTML_DATA'].'/'. $record;
        }, $mp3_records_urls);

        $transcription = $this->saveAndGetTranscriptions($mp3_records_full_urls, $mp3_records_urls[0]);

        // dodanie transkrypcji
            $mainContainerHtml = $crawler->filter('div.container')->html(); // ograniczenie do ważnego kontentu
            $domDocument = new \DOMDocument('1.0', 'UTF-8');
            @$domDocument->loadHTML(mb_convert_encoding($mainContainerHtml, 'HTML-ENTITIES', 'UTF-8'));

            $xpath = new \DOMXPath($domDocument);
            $audioTags = $xpath->query('//audio/source');
            foreach ($audioTags as $audioTag) {
                //dump($audioTag->getAttribute('src'));
                //dump($audioTag->parentNode->nodeName);
                $transcription = $domDocument->createElement('transcription', $transcription);
                $transcription->setAttribute('title', 'record-rafal_dyktafon');
                $audioTag->parentNode->appendChild($transcription); // added transcription
            }
        //

        // getQuestions
        $questions = file_get_contents('https://centrala.ag3nts.org/data/af693b93-4488-4f7a-811e-c0910ac17ba4/arxiv.txt');

            // build Payload
        $system = [[
            'role' => 'system',
            'content' => 'Poniżej znajdują się dane w formacje html, user doda ci obrazy.
            Twoim zadaniem jest opowiedzenie na następujące pytania (po jednym zdaniu) na podstawie otrzymanych danych:
            ###Questions###
            01=jakiego owocu użyto podczas pierwszej próby transmisji materii w czasie?
            02=Na rynku którego miasta wykonano testową fotografię użytą podczas testu przesyłania multimediów?
            03=Co Bomba chciał znaleźć w Grudziądzu?
            04=Resztki jakiego dania zostały pozostawione przez Rafała?
            05=Od czego pochodzą litery BNW w nazwie nowego modelu językowego?
            ###Data###'
            . mb_convert_encoding($domDocument->saveHTML(), 'UTF-8', 'HTML-ENTITIES')
        ]];


        $imagesData = array_map(function ($image) {
            return [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'name: '. substr($image,2)],
                    ['type' => 'image_url', 'image_url' => ['url'=> 'https://centrala.ag3nts.org/dane/'. $image]],
                ]
            ];
        }, $images_src);


        $messages = array_merge($system, $imagesData);
        //$response = $this->GPTservice->promptVisionModel($messages, 'gpt-4o-mini');
        //dd($response);
        $response = [
            '01' => 'Pierwszą próbę transmisji materii z użyciem owocu zrealizowano przy wykorzystaniu truskawki.',
            '02' => 'Testowa fotografia użyta podczas testu przesyłania multimediów została wykonana na rynku w Krakowie.',
            '03' => 'Rafał Bomba chciał znaleźć hotel w Grudziądzu.',
            '04' => 'Resztki pizzy zostały pozostawione przez Rafała.',
            '05' => 'Litery BNW w nazwie nowego modelu językowego pochodzą od „Brave New World”'
        ];
        //$response = json_encode($response);

        $answer = $this->aiDev3PreWorkService->answerToAiDevs(
            'arxiv',
            $response,
            $this->aiDevs3Endpoint['REPORT_URL']
        );


        dd($answer);



        var_dump(mb_convert_encoding($domDocument->saveHTML(), 'UTF-8', 'HTML-ENTITIES'));
        dd('test');





        /** add transcription bootom mp3 player */
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($domHtml);
        $xpath = new \DOMXPath($domDocument);
        $audioTags = $xpath->query('//audio');

        foreach ($audioTags as $audioTag) {
            dump($audioTag);
            //$audio = $domDocument->createElement('transcription');
        }

        dd($audio);

        $content = $crawler->filter('div.container')->html();
        dd($mp3_records_full_urls);


        dd($domHtml);


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

        list($record_url, $record, $savePath, $transcription) = $this->saveAndGetTranscriptions($mp3_records_full_urls, $mp3_records_urls[0]);

        /** add transcription bootom mp3 player */
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($mainContainerHtml);
        $xpath = new \DOMXPath($domDocument);
        //$images = $xpath->query('//div[@class="gallery"]/img');
        $images = $xpath->query('/audio');
        dd($images);


        $content = $crawler->filter('div.container')->html();
        dd($mp3_records_full_urls);


       print_r($response->getContent());



        return Command::SUCCESS;
    }

    private function getHtmlContent():string
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
        return $domHtml;
    }
    
    private function saveTranscription(){
        
    }

    private function test(): void
    {

        $html = <<<HTML
                    <html>
                        <body>
                            <div class="gallery" id="galleryId">
                                <img src="image1.jpg" alt="First image">
                                <img src="image2.jpg" alt="Second image">
                            </div>
                        </body>
                    </html>
                    HTML;
        // Tworzymy Crawler
        $crawler = new Crawler($html);

        // Konwertujemy HTML do DOMDocument (aby można było go edytować)
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($crawler->html());
        dump($crawler->html());

        // Pobieramy wszystkie obrazki w galerii
        $xpath = new \DOMXPath($domDocument);
        $images = $xpath->query('//div[@class="gallery"]/img');

        // Dodajemy opis pod każdym obrazkiem
        foreach ($images as $image) {
            // Tworzymy element <figcaption>
            $figcaption = $domDocument->createElement('figcaption', 'Opis obrazu: ' . $image->getAttribute('alt'));

            // Tworzymy kontener <figure> i dodajemy do niego obrazek i opis
            $figure = $domDocument->createElement('figure');
            $imageClone = $image->cloneNode(true); // Klonujemy obrazek
            $figure->appendChild($imageClone);
            $figure->appendChild($figcaption);

            // Zamieniamy oryginalny obrazek na kontener <figure>
            $image->parentNode->replaceChild($figure, $image);
            //$image->appendChild($figcaption);
        }

        dd($domDocument->saveHTML());
    }
}
