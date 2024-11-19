<?php

namespace App\Command;

use App\Service\Aidev3\AiDev3PreWorkService;
use App\Service\GPTservice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[AsCommand(name: 'app:makeTranscription', description: 'Make Transcription')]
class MakeTranscriptionCommand extends Command
{

    private GPTservice $GPTservice;
    private AiDev3PreWorkService $aiDev3PreWorkService;
    private mixed $aiDevs3Endpoint;

    public function __construct(
        GPTservice $GPTservice,
        AiDev3PreWorkService $aiDev3PreWorkService,
        ParameterBagInterface $parameterBag,
    )
    {
        parent::__construct();
        //print_r('Wiadomość z konstruktora');
        $this->GPTservice = $GPTservice;
        $this->aiDev3PreWorkService = $aiDev3PreWorkService;
        $this->aiDevs3Endpoint = $parameterBag->get('AI3_ENDPOINTS');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = "var/audioS2E1/przesluchania";
        $recordPath = 'var/audioS2E1/transcriptionRec.txt';

        if(strlen(file_get_contents($recordPath)) < 5000){
            file_put_contents($recordPath, '');
            foreach (scandir("var/audioS2E1/przesluchania") as $file) {
                if ($file === "." || $file === "..") {
                    continue;
                }

                $result = $this->GPTservice->makeTranscription($path . $file);

                $transcriptionRecords = file_put_contents($recordPath, $result, FILE_APPEND | LOCK_EX);
                dump($transcriptionRecords);
            }
        }

        $data = file_get_contents($recordPath);

        $chatAnswer = $this->GPTservice->prompt(
            "user poda ci zeznania czterech światków, odpowiedz na pytanie:  Na jakiej ulicy znajduje się uczelnia, na której wykłada Andrzej Maj. \n
            Pamiętaj, że zeznania świadków mogą być sprzeczne, niektórzy z nich mogą się mylić, a inni odpowiadać w dość dziwaczny sposób. \n
            Nazwa ulicy nie pada w treści zeznań. Musisz użyć swojej wiedzy _thinking \n
            Co ważne odpowiedz tylko nazwą ulicy",
            $data,
            'gpt-4o-mini'
        );

        $result = $this->aiDev3PreWorkService->answerToAiDevs('mp3', $chatAnswer, $this->aiDevs3Endpoint['REPORT_URL']);

        dump($result);


        return Command::SUCCESS; // Zwracanie kodu stanu - Command::SUCCESS oznacza, że wszystko poszło dobrze
    }

}