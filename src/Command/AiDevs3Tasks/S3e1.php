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

#[AsCommand(name: 'app:S3e1',description: 'Add a short description for your command')]
class S3e1 extends BaseCommand
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
        $directory_facts = 'var/AiDev3_data/S3E1/pliki_z_fabryki/facts';
        $files_facts = glob($directory_facts . '/*.txt',);

        $content_facts['facts'] = array_map(function ($file) {
            return [
                'file_facts_name' => basename($file),
                'content' => file_get_contents($file)
            ];
        }, $files_facts);

        $directory_documents = 'var/AiDev3_data/S3E1/pliki_z_fabryki';
        $files_documents = glob($directory_documents . '/*.txt',);

        $content_documents = array_map(function ($file) {
            return [
                'file_documents_name' => basename($file),
                'content' => file_get_contents($file)
            ];
        }, $files_documents);

        //dd(json_encode(array_merge($content_facts, $content_documents)));
        $system = 'Do każdego z 10 dokumentów wygeneruj słowa kluczowe w formie mianownika (czyli np. “sportowiec”, a nie “sportowcem”, “sportowców” itp.)
        Przy generowaniu metadanych posiłkuj się całą posiadaną wiedzą (czyli także plikami z faktami - facts)';

        $messages = [
            [
                'role' => 'system',
                'content' => $system
            ],
            [
                'role' => 'user',
                'content' => json_encode(array_merge($content_documents, $content_facts))
            ]
        ];
        //json_encode($messages);
        //dd(json_encode($messages));

        $answer = $this->GPTservice->simplePrompt($messages, 'gpt-4o-mini');
        dd($answer);






        //$file = file_get_contents();



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
