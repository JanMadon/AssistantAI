<?php

namespace App\Controller\AiDevs3;

use App\Service\Aidev3\AiDev3PreWorkService;
use App\Service\GPTservice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiDevsController extends AbstractController
{

    private ParameterBagInterface $config;
    private HttpClientInterface $httpClient;
    private AiDev3PreWorkService $aiDevsService;
    private GPTservice $gptService;

    public function __construct(
        ParameterBagInterface $config,
        HttpClientInterface   $httpClient,
        AiDev3PreWorkService  $aiDevsService,
        GPTservice            $gptService
    )
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->aiDevsService = $aiDevsService;
        $this->gptService = $gptService;
    }

    #[Route('/aidevs', name: 'aidevs3_prework_main', methods: ['GET'])]
    public function main(Request $request)
    {
        switch ($task = $request->query->get('task')) {
            case 'poligon':
                $result = $this->aiDevsService->poligon();

                $data['result'] = [
                    'task' => strtolower($request->query->get('task')),
                    'code' => $result['code'] ?? null,
                    'data' => $result['data'] ?? [],
                    'error' => $result['error'] ?? [],
                ];
                break;

            case 'login':
                $data = $this->aiDevsService->login();
                break;

            case 'auth':
                $data['text'] = $this->aiDevsService->auth();
                break;

            case 'checkData':
                $data['rawData'] = $this->aiDevsService->checkAndImprovedData();
                break;

            case 'personalData':
                $data['rawData'] = $this->aiDevsService->hidePersonalData();
                break;

            default:

        }

        return $this->render('aiDevs3/aidev3_prework_base.html.twig', $data ?? []);
    }

}