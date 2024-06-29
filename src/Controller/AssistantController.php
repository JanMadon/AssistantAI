<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Exception;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class AssistantController extends AbstractController
{

    #[Route('/assistant', 'assistant')]
    public function assistant(Request $request): Response
    {
        $number = random_int(0, 100);

        return $this->render('assistant.html.twig');
    }

    #[Route('/api/assistant/prompt', 'assistent_prompt')]
    public function assistantConversation(Request $request): Response
    {
        $request = json_decode($request->getContent());
        $preparedConversation = $this->prepareConverstionArray($request->conversation);

        $answer = $this->prompt($request->system, $preparedConversation);

        return new JsonResponse($answer, 200);
    }

    private function prepareConverstionArray(array $converstion): array
    {
        $preparedConversation = [];
        foreach ($converstion as $messages) {
            if (array_keys(get_object_vars($messages))[0] === 'User') {
                $preparedConversation[] = [
                    'role' => 'user',
                    'content' => $messages->User
                ];
            }
            if (array_keys(get_object_vars($messages))[0] === 'AI') {
                $preparedConversation[] = [
                    'role' => 'assistant',
                    'content' => $messages->AI
                ];
            }
        }

        return $preparedConversation;
    }


    private function prompt(string $system, array|string $contents)
    {

        try {
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
                'Authorization: Bearer ' . $this->getParameter('API_KEY_OPENAI')
            ]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);
            $response = json_decode($response)->choices[0]->message->content;
            curl_close($curl);
        } catch (\Exception $err) {
            $response = $err->getMessage();
        }
        return $response;
    }
}
