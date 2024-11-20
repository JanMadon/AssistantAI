<?php

namespace App\Command\AiDevs3Tasks;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:ai-devs-s2e3',description: 'Add a short description for your command',)]
class S2e3Command extends BaseCommand
{
   // public function __construct()
   // {
       // parent::__construct();
   // }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$io = new SymfonyStyle($input, $output);
        //$io->success('You have a new command! Now make it your own! Pass --help to see your options.');


        $request = $this->httpClient->request('GET', $this->aiDevs3Endpoint['S2E3_IMAGE_DESCRIPTION']);
        $description = json_decode($request->getContent())->description;

        $image = $this->GPTservice->imageGeneration($description);
        dump($image);

        $response = $this->aiDev3PreWorkService->answerToAiDevs(
            'robotid',
            'https://oaidalleapiprodscus.blob.core.windows.net/private/org-tKB7cdrESpihPWub4G0KdYxQ/user-H08M3nF3iuoShpQjBn4RMxrK/img-3SDWiH0lQb4r9mYnhYXB9zWV.png?st=2024-11-19T17%3A34%3A31Z&se=2024-11-19T19%3A34%3A31Z&sp=r&sv=2024-08-04&sr=b&rscd=inline&rsct=image/png&skoid=d505667d-d6c1-4a0a-bac7-5c84a87759f8&sktid=a48cca56-e6da-484e-a814-9c849652bcb3&skt=2024-11-18T19%3A23%3A17Z&ske=2024-11-19T19%3A23%3A17Z&sks=b&skv=2024-08-04&sig=jf2AIHdK6h5iWE4qamb%2BiYxzMSQqYt%2BuBbP9XUxTf4k%3D',
            $this->aiDevs3Endpoint['REPORT_URL']
        );

        dump($response);

        return Command::SUCCESS;
    }
}
