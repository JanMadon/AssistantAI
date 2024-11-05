<?php

declare(strict_types=1);

namespace App\Service\Aidev3;


use App\Service\GPTservice;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use DOMDocument;



class AiDev3PreWorkService
{
    private ParameterBagInterface $config;
    private HttpClientInterface $httpClient;
    private GPTservice $gptService;
    private array $AiDevs3Endpoint;

    public function __construct(ParameterBagInterface $config, HttpClientInterface $httpClient, GPTservice $gptService)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->gptService = $gptService;
        $this->AiDevs3Endpoint = $config->get('AI3_ENDPOINTS');
    }

    public function poligon(): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'https://poligon.aidevs.pl/dane.txt',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                    ]
                ]
            );
            $response = array_filter(explode("\n", $response->getContent()));

            $payload = [
                'task' => 'POLIGON',
                'apikey' => $this->config->get('API_KEY_AIDEVS'),
                'answer' => $response,
            ];

            $result = $this->httpClient->request(
                'POST',
                'https://poligon.aidevs.pl/verify',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($payload)
                ]
            );

            return [
                'server-code' => $result->getStatusCode(),
                'data' => json_decode($result->getContent(false)),
            ];
        } catch (
        RedirectionExceptionInterface|
        ClientExceptionInterface|
        ServerExceptionInterface|
        TransportExceptionInterface $e
        ) {
            $message = $e->getMessage();
        }
        return ['error' => $message];

    }

    public function login()
    {
        // todo obsługa błędów http
        $htmlDomContent = $this->httpClient->request('GET',$this->AiDevs3Endpoint['S1E1_LOGIN'])->getContent();

        $dom = new DOMDocument();
        $dom->loadHTML($htmlDomContent);
        $question = substr($dom->getElementById('human-question')->textContent, 9);

        $answer = $this->gptService->prompt(
            'Podaj rok jako cyfrę nic więcej nie zwracaj',
            $question,
        );


        $sendForm = $this->httpClient->request(
            'POST',
            $this->AiDevs3Endpoint['S1E1_LOGIN'], [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => [
                'username' => 'tester',
                'password' => '574e112a',
                'answer' => (int) $answer,
            ]
        ],
        );

        //dd($sendForm->getContent());

        return [
            'html' => $sendForm->getContent(),
            'question' => $question,
            'answer' => $answer,
        ];


    }

}
