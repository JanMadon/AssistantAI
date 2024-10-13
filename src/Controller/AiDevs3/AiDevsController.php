<?php

namespace App\Controller\AiDevs3;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiDevsController extends AbstractController
{

    private ParameterBagInterface $config;
    private HttpClientInterface $httpClient;

    public function __construct(ParameterBagInterface $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    #[Route('/aidevs', name: 'aidevs3_prework_main', methods: ['GET'])]
    public function main(Request $request)
    {
        switch ($request->query->get('task')) {
            case 'poligon':
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
                    'task' => strtoupper($request->query->get('task')),
                    'apikey' => $this->config->get('API_KEY_AIDEVS'),
                    'answer' => $response,
                ];
                dump($payload);
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
                $data['result'] = [
                    'task' => strtolower($request->query->get('task')),
                    'code' => $result->getStatusCode(),
                    'data' => json_decode($result->getContent(false))
                ];
                break;
            default:

        }

        return $this->render('aiDevs3/aidev3_prework_base.html.twig', $data ?? []);
    }

}