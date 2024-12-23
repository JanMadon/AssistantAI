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

        $content_documents['documents'] = array_map(function ($file) {
            return [
                'file_documents_name' => basename($file),
                'content' => file_get_contents($file)
            ];
        }, $files_documents);

        //dd(json_encode(array_merge($content_facts, $content_documents)));
        $system = 'Do każdego z 10 dokumentów wygeneruj słowa kluczowe w formie mianownika (czyli np. “sportowiec”, a nie “sportowcem”, “sportowców” itp.)
        Przy generowaniu metadanych posiłkuj się całą posiadaną wiedzą (czyli także plikami z faktami - facts) 
        ale słowa kluczowe generujesz tylko dla dokumentów.
        <exampleResponse>
        {
            "nazwa-pliku-01.txt":"lista, słów, kluczowych 1",
            "nazwa-pliku-02.txt":"lista, słów, kluczowych 2",
            "nazwa-pliku-03.txt":"lista, słów, kluczowych 3",
            "nazwa-pliku-NN.txt":"lista, słów, kluczowych N"
        }
         </exampleResponse>
         
         Słowa kluczowe są walidowane przez zewnętrzny system w przypadku błędu zwrócę błąd do ciebie i proszę o ponowne generowanie słów
         <errors>
         we cannot find a report on the capture of a teacher
         </errors>
        ';

        $system = 'Twoim zadaniem jest stworzenie listy słów kluczowych (w formie mianownika) na podstawie podanego przez użytkownika raportu.
        Na potrzeby poprawnego rozumienia treści raportów otrzymujesz poniższy konktest zawierający dodatkowe fakty oraz inne raporty:
         === KONTEKST ==='.
        json_encode(array_merge($content_documents, $content_facts))
         . '=== KONIEC KONTEKSTU === 
        Skup się na podanym raporcie i wygeneruj odpowiedź podając 10 słów kluczowych po przecinku.
        Dobrze wykorzystaj konteks aby wygenerować maksymanie precyzyjne słowa kluczowe  Naprzkład jeśli w mowa o osobie która jest wspominana w faktach, wykorzystaj wiedzę z faktów by stworzyć precyzyjne słowa kluczowe. Gdy pojawiają się nazyw własne, nazwiska - kluczowe informacje o tych osobach lub rzeczach (znajdujące się w faktach) powinny być zawarte w słowach kluczowych.
        Struktura odpowiedzi:
        {{
         "przemyślenia": "Podsumuj informacje z Raportu. Zastanów się jakie Fakty stanowią kontekst dla Raportu.",
         "refleksja-nazwy": "Czy w raporice padają nazwy własne? Korzystając z faktów jak mogę pogłębić moje słowa kluczowe?",
         "kluczowe-fakty": "Jakie informacje z FAKTÓW wzbogacają treść raportu?",
         "słowa-kluczowe": "10 słów kluczowych uwzględniających informacje z Raportu i Faktów"
        }}
        PRZYKŁAD:
        RAPORT:
        "Patrol Odnalazł Alicję AFD-1234 w stanie krytycznym w sektorze C4"
        FAKTY: "Alicja AFD-1234 jest prezydentem i szamanem, specjalista od języka Python"
        FAKTY: "Sektor C4 to opuszczony sektor rolniczy"
        {{
         "przemyślenia": "Raport mówi o stanie krytycznym Alicji AFD-1234. Kim ona jest? Jakie informacje z faktów mogą pomóc mi zrozumieć sytuację Alicji? Czy Fakty wspominają coś o sektorze C4?",
         "refleksja-nazwy": "Nazwy własne: Alicja AFD-1234, sektor C4. Jakie informacje z faktów mogą pomóc mi zrozumieć sytuację Alicji?",
         "kluczowe-fakty": "Alicja AFD-1234 jest prezydentem i szaman, specjalista od języka Python. Sektor C4 to opuszczony sektor rolniczy",
         "słowa-kluczowe": "Patrol, odnalezienie, Alicja AFD-1234, stan krytyczny, prezydent, kuternoga, Specjalista Python, sektor C4, opuszczony sektor rolniczy "
        }}';

        $messages = [
            [
                'role' => 'system',
                'content' => $system
            ],
//            [
//                'role' => 'user',
//                'content' => json_encode(array_merge($content_documents, $content_facts))
//            ]
        ];
        //json_encode($messages);
        //dd(json_encode($messages));

        $answerGPT = $this->GPTservice->simplePrompt($messages, 'gpt-4o');
        dd($answerGPT);

        //gpt-4o-mini
        $answerGPT = '{ 
            "2024-11-12_report-00-sektor_C4.txt": "jednostka, osobnik, skan, kontrola, patrol",
            "2024-11-12_report-01-sektor_A1.txt": "alarm, ruch, zwierzyna, obszar, patrol",
            "2024-11-12_report-02-sektor_A3.txt": "monitoring, aktywność, obiekt, patrol",
            "2024-11-12_report-03-sektor_A3.txt": "czujnik, wykrywanie, życie, patrol",
            "2024-11-12_report-04-sektor_B2.txt": "patrol, teren, anomalia, komunikacja",
            "2024-11-12_report-05-sektor_C1.txt": "sensor, detektor, aktywność, patrol, monitorowanie",
            "2024-11-12_report-06-sektor_C2.txt": "sektor, skanery, patrol, obszar",
            "2024-11-12_report-07-sektor_C4.txt": "czujnik, sygnał, nadajnik, analiza, patrol",
            "2024-11-12_report-08-sektor_A1.txt": "monitoring, obszar, czujnik, obserwacja",
            "2024-11-12_report-09-sektor_C2.txt": "patrol, peryferie, sygnał, obszar"
        }';

        //gpt-4o
        $answerGPT = '{
            "2024-11-12_report-00-sektor_C4.txt": "jednostka, fabryka, skan, Aleksander Ragowski, zgodność, patrol",
            "2024-11-12_report-01-sektor_A1.txt": "alarm, ruch, zwierzyna, obszar, patrol",
            "2024-11-12_report-02-sektor_A3.txt": "patrol, aktywność, monitoring, peryferia, obiekt",
            "2024-11-12_report-03-sektor_A3.txt": "patrol, czujniki, wykrywanie, życie organiczne, stan",
            "2024-11-12_report-04-sektor_B2.txt": "patrol, anomalia, sektor, kanały komunikacyjne",
            "2024-11-12_report-05-sektor_C1.txt": "aktywność, sensor, detektor, sygnał, patrol",
            "2024-11-12_report-06-sektor_C2.txt": "sektor, stan, skaner, temperatura, ruch, jednostka",
            "2024-11-12_report-07-sektor_C4.txt": "czujniki, sygnał, nadajnik, Barbara Zawadzka, analiza, patrol, incydenty",
            "2024-11-12_report-08-sektor_A1.txt": "monitoring, obszar, ruch, cisza, czujniki, wytyczne",
            "2024-11-12_report-09-sektor_C2.txt": "patrol, peryferia, czujniki, sygnały, anomalia, cykl"
        }';

        //gpt-4o
        $answerGPT = '{
            "2024-11-12_report-00-sektor_C4.txt":"jednostka, organika, Aleksander Ragowski, skan, dane, kontrola, patrol",
            "2024-11-12_report-01-sektor_A1.txt":"alarm, ruch, analiza, zwierzyna leśna, obszar, patrol",
            "2024-11-12_report-02-sektor_A3.txt":"obszar, patrol, aktywność, monitoring, peryferia, obiekt",
            "2024-11-12_report-03-sektor_A3.txt":"patrole, czujniki, wykrywanie, życie organiczne, stan",
            "2024-11-12_report-04-sektor_B2.txt":"patrol, zachód, anomalia, bezpieczeństwo, komunikacja",
            "2024-11-12_report-05-sektor_C1.txt":"aktywność, technologia, sensor, dźwięk, detektory, monitorowanie",
            "2024-11-12_report-06-sektor_C2.txt":"sektor, spokój, temperatura, ruch, operacyjność, patrol",
            "2024-11-12_report-07-sektor_C4.txt":"czujniki, ultradźwięki, nadajnik, Barbara Zawadzka, analiza, śledczy, zabezpieczenie",
            "2024-11-12_report-08-sektor_A1.txt":"monitoring, obszar, ruch, cisza, obserwacja, wytyczne",
            "2024-11-12_report-09-sektor_C2.txt":"patrol, peryferia, sygnał, anomalia, cykl, sektor"
        }';

        $response = $this->aiDev3PreWorkService->answerToAiDevs(
            'dokumenty',
            json_decode($answerGPT, true),
            $this->aiDevs3Endpoint['REPORT_URL']
        );
        dd($response);






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
