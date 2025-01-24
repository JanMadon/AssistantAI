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

#[AsCommand(name: 'app:S5e1', description: 'Week 5 / task monday')]
class S5e1 extends BaseCommand
{

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$this->makeRequest();

        $helper = $this->getHelper('question');
        if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to prepare conversation? (y/n)'))) {
            $phoneConversations = file_get_contents($this->aiDevs3Endpoint['S5E1_PHONE_CONVERSATIONS']);
            $phoneConversations_json = json_decode($phoneConversations);
            dump($phoneConversations_json);

            $messages = [
                ['role' => 'system', 'content' => file_get_contents('Prompts/AiDev3/S5E1/conversations_prompt.txt')],
                ['role' => 'user', 'content' => $phoneConversations],
            ];

            $answerGPT = $this->GPTservice->simplePrompt($messages, 'gpt-4o');
            dump($answerGPT); // gpt nie podołał tworzył
            file_put_contents('var/AiDev3_data/S5E1/conversation_phone.txt', $answerGPT);
        }

        $phoneConversationsPrepared = file_get_contents($this->aiDevs3Endpoint['S5E1_PHONE_CONVERSATIONS_PREPARED']);
        $phoneConversationsPrepared_json = json_decode($phoneConversationsPrepared);
        //dump($phoneConversationsPrepared_json);
        //dd($phoneConversationsPrepared_json);

        $stringConversations = '';
        foreach ($phoneConversationsPrepared_json as $key => $conversation) {
            $stringConversations .= "[$key]" . PHP_EOL . implode(PHP_EOL,$conversation) . PHP_EOL. PHP_EOL;
        }

        $facts = scandir('var/AiDev3_data/S5E1/facts');
        foreach ($facts as $key => $fact) {
            if($fact === '.' || $fact === '..') {
                continue;
            }
            $stringConversations .= "[facts $key]" . PHP_EOL . file_get_contents('var/AiDev3_data/S5E1/facts/'.$fact) . PHP_EOL. PHP_EOL;
        }
        //dd($stringConversations);


        $questions = file_get_contents($this->aiDevs3Endpoint['S5E1_PHONE_QUESTIONS']);
        $questions = json_decode($questions, true);

        if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to Ask gpt? (y/n)'))) {
            $messages[] = ['role' => 'system', 'content' => file_get_contents('Prompts/AiDev3/S5E1/prompt_to_question.txt') . $stringConversations];
            $answers = [];
            foreach ($questions as $key => $question) {
                if($key != '04') {
                    continue;
                }

                dump($question);
                $messages[] = ['role' => 'user', 'content' => $question];
                $answerGPT = $this->GPTservice->simplePrompt($messages, 'gpt-4o');
                dump($answerGPT);

                if($helper->ask($input, $output, new ConfirmationQuestion('Do you want to add gpt answer to answers? (y/n)'))) {
                    $answers[$key] = $answerGPT;
                }
            }
        }

        $answers = [
            "01" => "Samuel skłamał w rozmowie z Barbarą (rozmowa 5), mówiąc, że nie ma hasła do API. Jednak w rozmowie z Tomaszem (rozmowa 4) zdobył je i było to hasło NONOMNISMORIAR.",
            "02" => "https://rafal.ag3nts.org/b46c3",
            "03" => "Nauczyciel.",
            "04" => "Barbara i Samuel",
            "05" => $this->makeRequest()->message,
            "06" => "Aleksander"
        ];


        dump($answers);

        $askToAiDevs = $this->aiDev3PreWorkService->answerToAiDevs('phone', $answers);
        dump($askToAiDevs);

        return Command::SUCCESS;
    }

    private function makeRequest()
    {
        $payload = ['password' => 'NONOMNISMORIAR'];

        $response = $this->httpClient->request(
            'POST',
            'https://rafal.ag3nts.org/b46c3',
            [
                'headers' => ['content-type' => 'application/json'],
                'json' => $payload
            ]
        );
        return json_decode($response->getContent(false));
        //dd($response->getContent(false));
    }
}
