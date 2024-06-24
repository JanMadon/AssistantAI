<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class AssistantController extends AbstractController
{

    #[Route('/assistant')]
    public function assistant(Request $request): Response
    {
        //dd($request->request->all());

        $number = random_int(0, 100);

        return $this->render('assistant.html.twig');
    }

    #[Route('/api/assistant/prompt', 'assistent_prompt')]
    public function assistantConversation(Request $request): Response
    {
    
        $data = [
            'name' => 'test',
            'forname' => 'coś'
        ];

        return new JsonResponse($data);
    }


    private function prompt(string $system, array|string $contents)
    {
        $userContent = [];

        if (is_string($contents)) {
            $userContent[] = [
                'role' => 'user',
                'content' => $contents
            ];
            $contents =  $userContent;
        }

        $model = 'gpt-3.5-turbo';
        //$model = 'gpt-4';
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system
                ],
                ...$contents
            ]
        ];

        $payload = json_encode($payload);

        $curl = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'Authorization: Bearer ' . $this->conf['API_KEY_OPENAI']
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        echo curl_error($curl) ? 'Curl error: ' . curl_error($curl) : '';
        curl_close($curl);

        $response = json_decode($response)->choices;
        $response = (string)$response[0]->message->content;

        // dd($response);
        // echo "..........";

        return $response;
    }
}
