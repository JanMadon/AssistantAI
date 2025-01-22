<?php

namespace App\Command\AiDevs3Tasks;

use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:S2e4',description: 'Week 2 / task thursday')]
class S2e4 extends BaseCommand
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
        $path = 'var/AiDev3_data/pliki_z_fabryki';
        $files = scandir($path);

        $sortedFiles = [];
        /*
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $type = explode('.', $file)[1] ?? null;
            switch ($type) {
                case 'txt':
                    //dump( 'to jest pik z tekstem: '. $path.'/'.$file);
                    $content = file_get_contents($path.'/'.$file);
                    break;
                case 'png':
                    //dump('to jest pik z obrazem: '. $path.'/'.$file);
                    $content = file_get_contents($path.'/'.$file);
                    break;
                case 'mp3':
                    $content = $this->GPTservice->makeTranscription($path.'/'.$file);
                    //dump('to jest pik z nagraniem: ' . $path.'/'.$file);
                    break;
                default:
                    dump('nieznany plik');

            }

            if(!in_array($type, ['txt', 'png', 'mp3'])) {
                continue;
            }

            $prompt = "Jesteś zaawansowanym modelem językowym stworzonym do analizy treści plików.
             Twoim zadaniem jest sklasyfikowanie informacji zawartych w podanym tekście do jednej z trzech kategorii:

                people: Jeśli treść wyraźnie lub pośrednio odnosi się do ludzi, ich imion, zawodów, ról, zachowań, działań lub czegokolwiek związanego z działalnością człowieka.
                hardware: Jeśli treść opisuje maszyny, urządzenia, technologie, narzędzia lub procesy związane z systemami mechanicznymi lub komputerowymi.
                unknown: Jeśli treść nie odnosi się jednoznacznie ani do ludzi, ani do maszyn, lub jeśli jest niejasna lub nieistotna.
                Przeanalizuj poniższą treść i odpowiedz jedną z trzech kategorii (Ludzie, Maszyny lub Nieznane) wraz z krótkim uzasadnieniem swojego wyboru.
                ###
                co ważnie odpowiedz jednym słowem: 'people' lub 'hardware' lub 'unknown'
            ";

            //$prompt = 'Co widzisz na obrazie?';

            if($type === 'png') {
                $result = $this->GPTservice->promptImage($prompt, $content, 'gpt-4o-mini');
            } else {
                $result = $this->GPTservice->prompt($prompt, $content, 'gpt-4o');
            }
            dump($result);

            if(in_array($result, ['people', 'hardware'])) {
                $sortedFiles[$result][] = $file;
            }

        }
        */
        //dump($sortedFiles);
        $sortedFiles =
        [
            "people" =>  [ "2024-11-12_report-00-sektor_C4.txt", "2024-11-12_report-07-sektor_C4.txt", "2024-11-12_report-10-sektor-C1.mp3"],
            "hardware" => [ "2024-11-12_report-13.png", "2024-11-12_report-15.png", "2024-11-12_report-17.png"],
        ];

        $answer = $this->aiDev3PreWorkService->answerToAiDevs(
            'kategorie',
            $sortedFiles,
            $this->aiDevs3Endpoint['REPORT_URL']
        );
        dump($answer);

        return Command::SUCCESS;
    }
}
