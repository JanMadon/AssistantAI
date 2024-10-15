<?php

declare(strict_types=1);

namespace App\Service\Aidev3;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiDev3PreWorkService
{
    private ParameterBagInterface $config;
    private HttpClientInterface $httpClient;

    public function __construct(ParameterBagInterface $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
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

}
