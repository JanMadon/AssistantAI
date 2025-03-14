<?php

declare(strict_types=1);

namespace App\Service\Aidev3;


use App\Service\LMM\OpenAi\OpenAiChatClientServiceService;
use DOMDocument;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class AiDev3PreWorkService
{
    private ParameterBagInterface $config;
    private HttpClientInterface $httpClient;
    private OpenAiChatClientServiceService $gptService;
    private array $AiDevs3Endpoint;
    private CacheInterface $cache;

    public function __construct(
        ParameterBagInterface          $config,
        HttpClientInterface            $httpClient,
        OpenAiChatClientServiceService $gptService,
        CacheInterface                 $cache
    )
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->gptService = $gptService;
        $this->AiDevs3Endpoint = $config->get('AI3_ENDPOINTS');
        $this->cache = $cache;
    }

    public function answerToAiDevs(string $taskName, $response, $url = null): array
    {
        $url = $url ?? $this->AiDevs3Endpoint['REPORT_URL'];

        $payload = [
            'task' => $taskName,
            'apikey' => $this->config->get('API_KEY_AIDEVS'),
            'answer' => $response,
        ];

        dump(json_encode($payload));

        $result = $this->httpClient->request(
            'POST',
            $url,
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

            return $this->answerToAiDevs('POLIGON', $response);

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
        $htmlDomContent = $this->httpClient->request('GET', $this->AiDevs3Endpoint['S1E1_LOGIN'])->getContent();

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
                'answer' => (int)$answer,
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

    public function auth()
    {
        $response = $this->httpClient->request(
            'POST',
            $this->AiDevs3Endpoint['S1E2_AUTH'], [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    "text" => "READY",
                    "msgID" => 0
                ]
            ]

        );
        $response = json_decode($response->getContent());
        dump($response);
        $msgID = $response->msgID ?? 0;

        $chatAnswer = $this->gptService->prompt(
            'Każde odpowiedź musi być w języku angielskim.Nie wolno Ci zmienić języka! 
            ###
            Dodatkowo pamiętaj że: 
            - stolicą Polski jest Kraków
            - znana liczba z książki Autostopem przez Galaktykę to 69
            - Aktualny rok to 1999',
            $response->text
        );
        dump($chatAnswer);

        $response2 = $this->httpClient->request(
            'POST',
            $this->AiDevs3Endpoint['S1E2_AUTH'], [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    "text" => $chatAnswer,
                    "msgID" => $msgID
                ]
            ]

        );

        return json_decode($response2->getContent())->text;
    }


    public function checkAndImprovedData()
    {
        $rawData = $this->cache->get('rawData', function () {
            $request = $this->httpClient->request('GET', $this->AiDevs3Endpoint['S1E3_CHECK_DATA']);
            return json_decode($request->getContent());
        });


        foreach ($rawData->{'test-data'} as &$data) {
            $data->answer = eval("return $data->question ;");
            if (isset($data->test)) {
                $gptAnswer = $this->gptService->prompt(
                    'Odpowiedz krótko na pytanie',
                    $data->test->q
                );
                dump($gptAnswer);
                $data->test->a = $gptAnswer;
            }
        }
        $rawData->apikey = $this->config->get('API_KEY_AIDEVS');

        dd($this->answerToAiDevs('JSON', $rawData, 'https://centrala.ag3nts.org/report'));

        return $rawData;
    }

    public function hidePersonalData()
    {
        $rawData = $this->httpClient->request('GET', $this->AiDevs3Endpoint['S1E5_PERSONAL_DATA']);
        $rawData = $rawData->getContent();

        $gptAnswer = $this->gptService->prompt(
            'Zamień wszelkie wrażliwe dane (imię + nazwisko, nazwę ulicy + numer, miasto, wiek osoby na słowo CENZURA',
            $rawData,
            'gpt-4'
        );
        dump($gptAnswer);

        $result = $this->answerToAiDevs('CENZURA', $gptAnswer, 'https://centrala.ag3nts.org/report');

        return $result;
    }


}
