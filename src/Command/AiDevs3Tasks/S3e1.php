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
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(name: 'app:S3e1',description: 'Week 3 / task monday')]
class S3e1 extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to create key words? (y/n)'))){
            $keyWords = $this->prepareKeyWords();
            dd($keyWords);
        } else {
            /**
              @var $keyWords This is the result of keyword generation, requires manual correction
             */
            $keyWords = '{
            "2024-11-12_report-00-sektor_C4.txt": "sektor C4, nauczyciel, jednostka, organiczna, fabryka, osoba, Aleksander Ragowski, skan, biometryczny, dział, kontrola, patrol, techniki, inżynierowie, systemy, bezpieczeństwa, broń, eksperymenty, bezpieczeństwo, technologie, projekty, skupienie, automat, testy, raportowanie, monitoring, systemy biometryczne, dostęp, informacje",
            "2024-11-12_report-01-sektor_A1.txt": "alarm, ruch organiczny, zwierzyna leśna, obszar, patrol, spokój, sektor A1, roboty przemysłowe, jednostki wojskowe, montaż, produkcja, technicy, egzoszkielety, sztuczna inteligencja, monitoring, zabezpieczenia, technologie, bezpieczeństwo, wymagania wojskowe, innowacje, autonomiczność",
            "2024-11-12_report-02-sektor_A3.txt": "sektor A3, monitoring, patrol, obiekt, zadania, roboty, technicy, inżynierowie, systemy bezpieczeństwa, testy, produkcja, kontrola, egzoskielety, dostęp, technologie, sztuczna inteligencja, automatyzacja, efektywność, monitoring biometryczny, linie produkcyjne",
            "2024-11-12_report-03-sektor_A3.txt": "sektor A3, patrol, czujnik, życie, organiczne, odporność, techniki, montaż, robot, wojskowy, kontrola, jakości, sztuczna, inteligencja, system, monitoring, dostęp, zespół, inżynier, bezpieczeństwo, technik, egzoskielet, proces, test, komponent, produkcja, technologia, laboratoria",
            "2024-11-12_report-04-sektor_B2.txt": "sektor B2, prace badawczo-rozwojowe,nauczyciel, ogniwa bateryjne, wydajność energetyczna, inżynierowie, naukowcy, prototypy, trwałość, laboratoria, aparaty diagnostyczne, bezpieczeństwo, eksperymenty, technicy, sztuczna inteligencja, technologie szybkiego ładowania, mobilność, archiwizacja, innowacje, przewaga technologiczna",
            "2024-11-12_report-05-sektor_C1.txt": "sektor C1, aktywność organiczna, technologia, sensor, detektor, patrol, monitoring, materiały niebezpieczne, eksplozje, stanowiska testowe, systemy bezpieczeństwa, technicy, inżynierowie, identyfikatory, odzież ochronna, tajemnica, testy broni, archiwizacja, raportowanie",
            "2024-11-12_report-06-sektor_C2.txt": "sektor C2, obszar, skanery, temperatura, ruch, jednostka, patrol, bezpieczeństwo, broń, testowanie, tajność, technicy, inżynierowie, identyfikatory, odzież, prace, archiwizacja, raportowanie, eksperymenty, laboratoria, technologie, ogniwa, badania, zespół, inżynier, naukowiec, prototypy, pojemność, trwałość, warunki, algorytmy, sztuczna inteligencja, mobilność, maszyny, monitoring, zabezpieczenia, audyt, kontrola, innowacje, przyszłość, energetyka, połączenie, projekty, ruch oporu, automatyzacja, technologia, zakłady, produkcja, systemy, identyfikacja, komponenty, kontrola jakości, testy, renowacja, infrastruktura, strefa, komora, bezpieczeństwo, eksperyment, materiał, zmiany, wizje, niepokój, umysł, analizy, zaufanie, szkolenie, agent",
            "2024-11-12_report-07-sektor_C4.txt": "programista JavaScript,  czujniki, sygnał, nadajnik, krzaki, las, analiza, odciski, palce, osoba, Barbara Zawadzka, dział, patrol, incydent, sektor C4, testowanie, broń, bezpieczeństwo, materiały, eksplozje, stanowiska, monitorowanie, dostęp, technicy, inżynierowie, identyfikatory, archiwizacja, raportowanie",
            "2024-11-12_report-08-sektor_A1.txt": "sektor A1, monitoring, ruch, cisza, czujniki, obserwacja, teren, wytyczne",
            "2024-11-12_report-09-sektor_C2.txt": "sektor C2, obszar, czujniki, sygnały, anomalia, cykl, broń, systemy, bezpieczeństwo, testy, stanowiska, technicy, inżynierowie, identyfikatory, odzież, archiwizacja, raportowanie"
            }';
        }
        dump(json_decode($keyWords));

        $response = $this->aiDev3PreWorkService->answerToAiDevs(
            'dokumenty', json_decode($keyWords));
        dump($response);

        return Command::SUCCESS;
    }

    private function prepareKeyWords(): array
    {
        $prompts_directory = 'Prompts/AiDev3/S3E1';
        $system = file_get_contents($prompts_directory.'/simple2.txt');

        $directory_facts = 'var/AiDev3_data/S3E1/pliki_z_fabryki/facts';
        $files_facts = glob($directory_facts . '/*.txt',);

        $content_facts = array_map(function ($file) {
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

        $allKeyWords = [];
        foreach ($content_documents as &$content_document)
        {
            dump($content_document);

            $content_document['facts'] = $content_facts;
            $oneDocumentKeyWords = $this->GPTservice->oneShootPrompt($system, json_encode($content_document), 'gpt-4o-mini', true);
            $allKeyWords[] = $oneDocumentKeyWords;

            dump($oneDocumentKeyWords);
            dump('--------------------------NEXT DOCUMENT------------------------');
        }

        return $allKeyWords;
    }


}
