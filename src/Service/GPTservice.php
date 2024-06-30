<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GPTservice {
 
    //private string $model;
    private ParameterBagInterface $config;

    public function __construct(ParameterBagInterface $config)
    {
        //$this->model = $model;
        $this->config = $config;
    }

    public function prompt(string $model, string $system, array|string $contents)
    {
        $contents = $this->prepareConverstionArray($contents);

        try {
            $userContent = [];
            if (is_string($contents)) {
                $userContent[] = [
                    'role' => 'user',
                    'content' => $contents
                ];
                $contents =  $userContent;
            }

            //$model = 'gpt-3.5-turbo';
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
                'Authorization: Bearer ' . $this->config->get('API_KEY_OPENAI')
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
}

