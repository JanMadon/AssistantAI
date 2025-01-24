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

#[AsCommand(name: 'app:S3e1',description: 'Week 3 / task monday')]
class S3e1 extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /*
        $prompts_directory = 'Prompts/AiDev3/S3E1';
        $system = file_get_contents($prompts_directory.'/simple2.txt');

        $directory_facts = 'var/AiDev3_data/S3E1/pliki_z_fabryki/facts';
        $files_facts = glob($directory_facts . '/*.txt',);

        $content_facts = array_map(function ($file) {
            //return [
                //'file_facts_name' => basename($file),
                //'content' => file_get_contents($file)
                //basename($file) => file_get_contents($file),
            //];
            return file_get_contents($file);
        }, $files_facts);

        $directory_documents = 'var/AiDev3_data/S3E1/pliki_z_fabryki';
        $files_documents = glob($directory_documents . '/*.txt',);

        $content_documents = array_map(function ($file) {
            return [
                'file_documents_name' => basename($file),
                'content_document' => file_get_contents($file)
            ];
        }, $files_documents);

        $answers = [];
        foreach ($content_documents as &$content_document)
        {
            $content_document['facts'] = $content_facts;
            $messages = [
                [
                    'role' => 'system',
                    'content' => $system
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($content_document)
                ]
            ];;

            //$temp = $this->GPTservice->simplePrompt($messages, 'gpt-4o-mini');
            //dump($temp);
            //$answers[] = $temp;
        }
        */


        $answerGPT = '{
            "2024-11-12_report-00-sektor_C4.txt": "raport, sektor C, jednostka organiczna, Aleksander Ragowski, skan biometryczny, dział kontroli, radar, automatyzacja, technologia, roboty, broń, programowanie, Java, sztuczna inteligencja, ruch oporu, Barbara Zawadzka, front-end development, Krytyka, systemy monitorujące, każda strefa, eksperymenty, wybuchy, eksperymenty, technologia, ogniwa, laboratoria, przekroczenie, bezpieczeństwo, Rafał Bomba, techniki, Adam Gospodarczyk, D, magazyn, bezpieczeństwo, kontrola, komponenty, przemysł, nieautoryzowany dostęp.",
            "2024-11-12_report-01-sektor_A1.txt": "alarm, ruch, organiczny, analiza, zwierzyna, obszar, patrol, spokój, sektor, zabezpieczenie, testowanie, broń, pomieszczenie, system, bezpieczeństwo, technologii, pracownik, tajne, montaż, robot, wojskowy, produkcja, kontrola, jakość, inżynier, sztuczna inteligencja, egzoszkielet, biometryczny, dostęp, projekt, ogniwa, bateria, badania, laboratoryjny, władza, opozycja, programowanie, Ragowski, Zawadzka, Azazel, doświadczenie, Adam, Gospodarczyk, przeszłość, technologia, ryzyko, eksperyment, skład, kontrola, monitoring, zakład, materiał, komponent, magazyn, systemy, operacyjny, roboty, maszyna, infrastruktura, zasoby, zabezpieczenia, zespół, naukowiec, laborant, asystent, badania, zaufanie, strategia, innowacja, sabotować, rząd, agent, ruch, przeciwnik, pieczenie, pizza, ananas, chaos, umiejętności, sabotować, techniki, prank, wybuch, mikroskopijny, eksperyment, maszyn.",
            "2024-11-12_report-02-sektor_A3.txt": "raport, sektor A, sektor B, sektor C, sektor D, monitoring, technologia, broń, roboty, zespół, badania, eksperymenty, bezpieczeństwo, kontrola, sztuczna inteligencja, programowanie, Algorytm, bateria, automatyzacja, ruch oporu, Aleksander Ragowski, Barbara Zawadzka, Azazel, Rafał Bomba, Adam Gospodarczyk.",
            "2024-11-12_report-03-sektor_A3.txt": "sektor A, sektor B, sektor C, sektor D, patrol, czujniki, życie organiczne, nowoczesna broń, systemy bezpieczeństwa, technicy, inżynierowie, roboty przemysłowe, testy broni, inteligencja sztuczna, egzoszkielety, monitoring, dane, eksperymenty, ogniwa bateryjne, laboratoria, bezpieczeństwo, prototypy, technologie, ruch oporu, programowanie, algorytmy, front-end development, maszyny, edukacja, krytyka, aresztowanie, Azazel, teleportacja, Zygfryd, Rafał Bomba, nanotechnologia, bezpieczeństwo, inżynieria, kodowanie, umiejętności, Adam Gospodarczyk, agent, kontrola, monitorowanie, magazyn, sprzęt, komponenty.",
            "2024-11-12_report-04-sektor_B2.txt": "sektor B2, zachodnia część, teren, anomalie, odchylenia, normy, bezpieczeństwo, kanały komunikacyjne, punkt, Sektor C, obszar, broń, pomieszczenie, systemy bezpieczeństwa, materiały niebezpieczne, eksplozje, stanowiska testowe, ściany, osłony, automatyczne systemy monitorujące, test, dane, wstęp, technicy, inżynierowie, identyfikatory, odzież ochronna, prace, tajne, archiwizacja, raportowanie, Sektor A, montaż, roboty przemysłowe, jednostki wojskowe, działania bojowe, strefy, produkcyjne, komponenty, jakości, testy, konstrukcja, tajność, kontrola, sztuczna inteligencja, egzoszkielety, monitoring, ruch, anomalie, biometryczne, technologie, sztuczna inteligencja, baterie, wydajność, przemysł, zastosowania militarne, zespoły, inżynierowie, naukowcy, prototypy, gęstość, energia, pojemność, trwałość, warunki, aparaty diagnostyczne, struktury materiałów, bezpieczeństwo, eksperymenty, ryzyko, wybuchy, toksyczne gazy, komory, wyposażenie ochronne, wysokie temperatury, algorytmy, ładowania, regeneracji, mobilność, ograniczony dostęp, archiwizacja, przewaga technologiczna, Aleksander Ragowski, nauczyciel, języka angielskiego, Grudziądz, metody nauczania, zaangażowanie, edukacja, automatyzacja, rząd robotów, krytyka, aresztowanie, ucieczka, informatorzy, programowanie, stan psychiczny, opozycyjne, umiejętności, systemy rządowe, Barbara Zawadzka, frontend development, branża IT, automatyzacja, firma, kontrola sztucznej inteligencji, ruch oporu, JavaScript, Python, AI Devs, bazy wektorowe, algorytmy, walka wręcz, krav maga, techniki samoobrony, broń palna, koktajle Mołotowa, kontrasty, pizza, Gospodarczyk, programowanie, umiejętności, rekrutacyjne, techniki, sztuczna inteligencja, Azazel, podróżnik, teleportacja, technologie, systemy operacyjne, nadprzyrodzone, Zygfryd, mocodawca, badania, Rafał Bomba, laborant, badania, sztuczna inteligencja, nanotechnologia, asystent, eksperymenty, niewłaściwe, zaufanie, Adam Gospodarczyk, zakłady, kontrole, magazyn, produkcja, urządzenia, elastyczny, strefa laboratoryjna.",
            "2024-11-12_report-05-sektor_C1.txt": "Sektor C, monitoring, bezpieczeństwo, testowanie, broń, materiały niebezpieczne, eksperymenty, roboty, przemysł, technologie, programowanie, sztuczna inteligencja, ruch oporu, inżynierowie, technicy, identyfikatory, archiwizacja, raportowanie, sektor A, sektor B, sektor D, badania, ogniwa bateryjne, naukowcy, laboratoria, prototypy, mobilność, Adam Gospodarczyk, Aleksander Ragowski, Barbara Zawadzka, Rafał Bomba, Azazel, Zygfryd, kontrola, jakość, zaginięcie, eksperymenty, umiejętności, tajne, zbrodnia, opozycja.",
            "2024-11-12_report-06-sektor_C2.txt": "sektor C, skanery, temperatura, ruch, jednostka, patrol, obszar, broń, systemy bezpieczeństwa, materiały niebezpieczne, eksplozje, stanowiska testowe, technicy, inżynierowie, identyfikatory, odzież ochronna, prace tajne, archiwizacja, raportowanie, sektor A, montaż, roboty przemysłowe, jednostki wojskowe, strefy, linie produkcyjne, komponenty, rygorystyczna kontrola, testy, bezpieczeństwo, laboratoria, ogniwa bateryjne, zespół inżynierów, naukowców, prototypy, eksperymenty, komory zabezpieczone, obrażenia, baterie, mobilność, Adam Gospodarczyk, programowanie, ryzyko, systemy biometryczne, automatyzacja, Barbara Zawadzka, software, ruch oporu, Azazel, teleportacja, Zygfryd, Rafał Bomba, sztuczna inteligencja, nanotechnologia, tajne eksperymenty, inżynierowie, laboratoria, komponenty, surowce.",
            "2024-11-12_report-07-sektor_C4.txt": "sygnał, nadajnik, czujnik, obiekt, analiza, odcisk, palec, osoba, imię, Barbara Zawadzka, baza, urodzeń, patrol, incydent, sektor, obszar, bezpieczeństwo, broń, pomieszczenie, system, monitorowanie, testy, produkcja, robot, montaż, technika, władza, ruch, opór, automatyzacja, spotkanie, zagrożenie, edukacja, algorytm, krytyka, aresztowanie, tożsamość, zniknięcie, nowoczesna, technologia, bateria, laboratorium, eksperyment, ryzyko, wybuch, gaz, biometria, mobilność, projekt, energii, Azazel, postać, przeszłość, teleportacja, systemy, operacyjny, Rafał Bomba, laborant, eksperyment, czas, nanotechnologia, zdrowie, psychiczne, zjawisko, pamięć, rzeczywistość, Gospodarczyk, programowanie, rekrutacja, agent, techniki, bypassowanie, ruch, kontrola, porzucenie, tajemnica, zakład, strategia, materiały, magazyn.",
            "2024-11-12_report-08-sektor_A1.txt": "monitoring, obszar, patrol, ruch, aktywność, obserwacja, sektor, fabryka, broń, bezpieczeństwo, materiał, eksplozje, stanowisko, test, dane, technicy, inżynierowie, identyfikator, odzież, prace, tajność, archiwizacja, raportowanie, montaż, roboty, jednostki, bojowe, strefy, produkcja, komponenty, kontrola, jakość, wytrzymałość, sztuczna, inteligencja, egzoszkielety, monitoring, świat, technologia, ogniwa, energia, badania, laboratoria, eksperymenty, bezpieczeństwo, wybuch, gazy, prototypy, mobilność, szkolenie, umiejętności, programowanie, Java, JavaScript, Python, sztuczna, inteligencja, koktajl, Mołotow, Azazel, teleportacja, technologia, systemy, programowanie, agent, Gospodarczyk, rekrutacja, struktura, ruch, opór, Sektor A, Sektor B, Sektor C, Sektor D",
            "2024-11-12_report-09-sektor_C2.txt": "sektor C, obszar, anomalia, systemy bezpieczeństwa, testowanie, nowoczesna broń, prowadzanie prób, technicy, inżynierowie, archiwizacja, raportowanie, sektor A, montaż, roboty przemysłowe, jednostki wojskowe, przestrzeń, strefy, linie produkcyjne, komponenty, kontrola jakości, robot wojskowy, sztuczna inteligencja, egzoszkielety, monitoring, biometryczne systemy, technologia, sektor B, ogniwa bateryjne, zespół, inżynier, naukowcy, materiały, laboratoria, aparaty diagnostyczne, zabezpieczenie, ryzyko, eksperymenty, testowanie, wysoka gęstość energetyczna, mobilność, Adam Gospodarczyk, Aleksander Ragowski, Barbara Zawadzka, Azazel, Rafał Bomba, Zygfryd, ruch oporu, programowanie, hacker, sztuczna inteligencja, nieautoryzowany dostęp."
        }';

        $response = $this->aiDev3PreWorkService->answerToAiDevs(
            'dokumenty',
            json_decode($answerGPT, true),
            $this->aiDevs3Endpoint['REPORT_URL']
        );
        dd($response);



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
