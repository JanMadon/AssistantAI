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

#[AsCommand(name: 'app:S4e1', description: 'Add a short description for your command')]
class S4e1 extends BaseCommand
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
        // REPAIR, DARKEN, BRIGHTEN

        $photosInfo = $this->aiDev3PreWorkService->answerToAiDevs('photos', 'start');
        dump($photosInfo);
        $photoNames = $this->GPTservice->oneShootPrompt(file_get_contents('Prompts/AiDev3/S4E1/prompt_getImages.txt'), $photosInfo['data']->message);
        $photoNames = json_decode($photoNames);
        dump($photoNames);
        if($photoNames[0] == 'null'){
            return Command::FAILURE;
        }

        //preg_match_all('/https?:\/\/[^\s]+/i', $photosInfo['data']->message, $matches);
        //$path = dirname($matches[0][0]) . '/';
//        $imageNames = [];
//        foreach ($matches[0] as $match) {
//            $imageNames[] = basename($match);
//        }

        $helper = $this->getHelper('question');
        $prompt = file_get_contents('Prompts/AiDev3/S4E1/prompt.txt');

        $path = 'https://centrala.ag3nts.org/dane/barbara/';

        $correct_photos = [];
        foreach ($photoNames as $imageName) {
            do {
                dump($path.$imageName);
                if (!$helper->ask($input, $output, new ConfirmationQuestion('Is this url ok ? (y/n)'))) {
                    $brake = true;
                    break;
                }

                $gptResponse = $this->GPTservice->promptVisionModel($this->prepareMessage($prompt, $path.$imageName));
                dump($gptResponse);

                if (!$helper->ask($input, $output, new ConfirmationQuestion('Did the model choose the correct tool? (y/n)'))) { // czasami model mini chce poprawiać zdjęcie, które jest ok.
                    if ($helper->ask($input, $output, new ConfirmationQuestion('Is correct photo? (y/n)'))) {
                        $gptResponse = 'OK';
                    } else {
                        break;
                    }
                }

                if($gptResponse == 'OK'){
                    $correct_photos[] = $path.$imageName;
                    break;
                }

                $photosInfo = $this->aiDev3PreWorkService->answerToAiDevs('photos', $gptResponse . ' ' . $imageName);
                dump($photosInfo);

                if (!$helper->ask($input, $output, new ConfirmationQuestion('Is this the expected answer? (y/n)'))) {
                    break;
                }

                $photoNames = $this->GPTservice->oneShootPrompt(file_get_contents('Prompts/AiDev3/S4E1/prompt_getImages.txt'), $photosInfo['data']->message);
                $imageName = json_decode($photoNames)[0];
                dump($imageName);
                if($imageName == 'null'){
                    return Command::FAILURE;
                }

            } while($helper->ask($input, $output, new ConfirmationQuestion('Would you like to continue?? (y/n)')));
            print_r(PHP_EOL.'-- NEXT PHOTO --' . PHP_EOL);
        }

        $answerDescription = $this->GPTservice->promptVisionModel($this->prepareDescription($correct_photos), 'gpt-4o');
        dump($answerDescription);

        $answer = $this->aiDev3PreWorkService->answerToAiDevs('photos', $answerDescription);
        dump($answer);

        return Command::SUCCESS;
    }

    private function prepareDescription(array $imageUrls)
    {
        dump($imageUrls);
        $content = [['type' => 'text', 'text' => 'Opisz szczegółowo kobietę, która powtarza się na zdjęciach']];

        foreach ($imageUrls as $url) {
            $content[] = ['type' => 'image_url', 'image_url' => ['url' => $url]];
        }

        $messages = [
            [
                'role' => 'user',
                'content' => $content,
            ]
        ];

        return $messages;

    }

    private function prepareMessage($prompt, $imageUrl)
    {
        $message = [
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $prompt],
                    ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                ],
            ]
        ];

        return $message;
    }


}
