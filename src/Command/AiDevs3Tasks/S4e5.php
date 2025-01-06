<?php

namespace App\Command\AiDevs3Tasks;

use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use function PHPUnit\Framework\isNull;
use function Symfony\Component\Translation\t;

#[AsCommand(name: 'app:S4e5', description: 'Add a short description for your command')]
class S4e5 extends BaseCommand
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
        $helper = $this->getHelper('question');
        if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to prepare transcription from images? (y/n)'))) {
            $this->prepareImageTranscription();
        }


        $messages = [
            [
                'role'=>'system',
                'content'=>'Poniżej przedstawiam Ci przepisaną treść notesu Rafała. Jest tam sporo niejasności, a sposób formułowania myśli przez autora jest dość… osobliwy.
                    Mając te informacje spróbuj odpowiedzieć na pytanie użytkownika. '
            ]
        ];

        $content =[];

        $files = scandir('var/AiDev3_data/S4E5');
        foreach ($files as $file) {
            if(!str_contains($file, '.txt')) {
                continue;
            }

            $content[] = '['.str_replace('.txt', '', $file).'] '.PHP_EOL . file_get_contents('var/AiDev3_data/S4E5/'.$file);
        }

        $messages = [
            [
                'role'=>'system',
                'content'=>'Poniżej przedstawiam Ci przepisaną treść notesu Rafała. Jest tam sporo niejasności, a sposób formułowania myśli przez autora jest dość… osobliwy.
                    Mając te informacje spróbuj odpowiedzieć na pytanie użytkownika (nawet jeśli nie jest to bezpośrednio podane postaraj się wywnioskować odpowiedz). 
                    
                    ###Content###
                    '.implode(PHP_EOL.PHP_EOL, $content)
            ],
        ];

        $questions = file_get_contents($this->aiDevs3Endpoint['S4E5_QUESTION_JSON']);
        $questions = json_decode($questions, true);

//        $answers = [];
//        foreach ($questions as $key => $question) {
//            $messages[] = ['role' => 'user', 'content' => 'Jakie miejscowości zawartę są w notatniku'];
//            $answerGPT = $this->GPTservice->simplePrompt($messages, 'gpt-4o');
//            dump($answerGPT);
//
//            if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to add gpt answer to answers? (y/n)'))) {
//                $answers[$key] = $answerGPT;
//            }
//        }
//        dd($answers);

        $answers = [
            '01' => '2019',
            '02' => 'Adam',
            '03' => 'W jaskini',
            '04' => '2024-11-12',
            '05' => 'Lubawa',
        ];

        $askToAiDevs = $this->aiDev3PreWorkService->answerToAiDevs('notes', $answers);

        dump($askToAiDevs);

        return Command::SUCCESS;
    }

    private function prepareImageTranscription()
    {
        $files = scandir('var/AiDev3_data/S4E5');
        dump($files);
        foreach ($files as $file) {
            if(!str_contains($file, '.png')) {
                continue;

            }
            if(in_array(str_replace('.png', '.txt', $file), $files)){
                continue;
            }
            dump('var/AiDev3_data/S4E5/'.$file);

            $txtFromGpt = $this->GPTservice->promptImage(
                'Przepisz text z obrazka. Zwróć jedynie ten text (bez komentarzy)',
                'var/AiDev3_data/S4E5/'.$file,
            );
            dump($file.': '. $txtFromGpt);

            if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to save text content? (y/n)'))) {
                touch('var/AiDev3_data/S4E5/'.str_replace('.png', '.txt', $file));
                file_put_contents('var/AiDev3_data/S4E5/'.str_replace('.png', '.txt', $file), $txtFromGpt);
            }
            dump('---- Next image -----');
        }
    }
}
