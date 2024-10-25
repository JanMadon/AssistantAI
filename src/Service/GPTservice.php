<?php

namespace App\Service;

use Couchbase\HttpException;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;

;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
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

    public function prompt(string $system, array $contents, string $model = 'gpt-3.5-turbo', $config = [])
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
            $response = $this->httpClient->request('POST', $this->url, [
                'json' => $payload, // symfony sam konwertuje na jsona
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Content-Length: ' . strlen(json_encode($payload)),
                    'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI')
                ]
            ]);

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

    private function prepareConversationArray(array $conversation): array
    {
        $preparedConversation = [];
        foreach ($conversation as $messages) {
            //dd();

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

