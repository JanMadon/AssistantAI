<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class GPTservice
{

    private string $url = 'https://api.openai.com/v1/chat/completions';
    //private string $model;
    private ParameterBagInterface $config;
    private HttpClientInterface $httpClient;


    public function __construct(ParameterBagInterface $config, HttpClientInterface $httpClient)
    {
        //$this->model = $model;
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    public function prompt(string $system, array|string $contents, string $model = 'gpt-3.5-turbo', $config = '')
    {
        $contents = $this->prepareConversationArray($contents);

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

        if(property_exists($config, 'temperature')){
            $payload['temperature'] = (float) $config->temperature;
        }

        try {
            $response = $this->httpClient->request(
                'POST',
                $this->url,
                    [
                        'json' => $payload, // symfony sam konwertuje na jsona
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'Content-Length: ' . strlen(json_encode($payload)),
                            'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI')
                        ]
                    ]
                );

            //dd($response->getContent(false));
            $response = json_decode($response->getContent(false))->choices[0]->message->content;
        } catch (ClientException $exception) {
            $response = $exception->getMessage();
        }  catch (HttpExceptionInterface $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = 'Unexpected error: ' . $exception->getMessage();
        }

        return $response;
    }

    public function promptImage(string $prompt, string $imagePath, string $model = 'gpt-4o-mini')
    {
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            "type" => "text",
                            "text" => $prompt
                        ],
                        [
                            "type" => "image_url",
                            "image_url" => [ "url" => "data:image/jpeg;base64," . base64_encode($imagePath) ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->httpClient->request(
                'POST',
                $this->url,
                [
                    'json' => $payload,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Content-Length: ' . strlen(json_encode($payload)),
                        'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI')
                    ]
                ]
            );
            $response = json_decode($response->getContent(false))->choices[0]->message->content;
        } catch (ClientException $exception) {
            $response = $exception->getMessage();
        }  catch (HttpExceptionInterface $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = 'Unexpected error: ' . $exception->getMessage();
        }

        return $response;
    }

    public function makeTranscription(string $filePath): string
    {
        try{
            $response = $this->httpClient->request(
                'POST',
                'https://api.openai.com/v1/audio/transcriptions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI'),
                        'Content-Type' => ' multipart/form-data'
                    ],
                    'body' => [
                        'file' => fopen($filePath, 'r'),
                        'model' => 'whisper-1'
                    ]
                ]

            );
            $response = $response->getContent(false);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $response = $e->getMessage();
        }
        return $response;
    }



    public function imageGeneration($prompt)
    {
        $payload = [
            'model'=>'dall-e-3',
            'prompt'=>$prompt,
            'n' => 1,
            'size' => '1024x1024' // 1024x1024, 1024x1792 or 1792x1024
        ];

        try{
            $response = $this->httpClient->request(
                'POST',
                'https://api.openai.com/v1/images/generations',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI'),
                        'Content-Type' => 'application/json'
                    ],
                    'json' => $payload
                ]

            );
            $response = $response->getContent(false);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $response = $e->getMessage();
        }
        return json_decode($response)->data[0]->url;
    }



    function makeEmbeding($input): array
    {
        $payload = [
            'model' => 'text-embedding-ada-002',
            'encoding_format' => 'float',
            'input' => json_encode($input)
        ];

        try {

            $request = $this->httpClient->request(
                'POST',
                'https://api.openai.com/v1/embeddings',
                [
                    'body' => $payload,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Content-Length: ' . strlen(json_encode($payload)),
                        'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI')
                    ]
                ]

            );

            $response = json_decode($request->getContent(false))->data[0]->embedding;;

        } catch (ClientException $exception) {
            $response = $exception->getMessage();
        }  catch (HttpExceptionInterface $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = 'Unexpected error: ' . $exception->getMessage();
        } 

        return (array) $response;
    }

    public function getChatModels()
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'https://api.openai.com/v1/models',
                [
                   'headers' => [
                       'Accept' => 'application/json',
                       'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI')
                   ]
                ]);
            $response = json_decode($response->getContent(false));
        } catch (ClientException $exception) {
            $response = $exception->getMessage();
        }  catch (HttpExceptionInterface $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = 'Unexpected error: ' . $exception->getMessage();
        } catch (TransportExceptionInterface $e) {
        }

        return $response->data ?? null;
    }

    private function prepareConversationArray(array|string $conversation): array
    {
        if(is_string($conversation)){
            return [[
                'role'=>'user',
                'content'=> $conversation
            ]];
        }

        $preparedConversation = [];
        foreach ($conversation as $messages) {

            if (property_exists($messages, 'User')) {
                $preparedConversation[] = [
                    'role' => 'user',
                    'content' => $messages->User
                ];
            }
            if (property_exists($messages, 'AI')) {
                $preparedConversation[] = [
                    'role' => 'assistant',
                    'content' => $messages->AI
                ];
            }
        }

        return $preparedConversation;
    }

}

