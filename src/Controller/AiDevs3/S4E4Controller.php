<?php

namespace App\Controller\AiDevs3;

use App\Service\Aidev3\AiDev3PreWorkService;
use App\Service\LMM\OpenAi\OpenAiChatClientServiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class S4E4Controller extends AbstractController
{
    protected OpenAiChatClientServiceService $GPTservice;
    protected AiDev3PreWorkService $aiDev3PreWorkService;
    protected array $aiDevs3Endpoint;
    protected HttpClientInterface $httpClient;
    protected ParameterBagInterface $envParma;

    public function __construct(
        OpenAiChatClientServiceService $GPTservice,
        AiDev3PreWorkService           $aiDev3PreWorkService,
        ParameterBagInterface          $parameterBag,
        HttpClientInterface            $httpClient,
        CacheInterface                 $cache
    )
    {
        $this->GPTservice = $GPTservice;
        $this->aiDev3PreWorkService = $aiDev3PreWorkService;
        $this->aiDevs3Endpoint = $parameterBag->get('AI3_ENDPOINTS');
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->envParma = $parameterBag;
    }

    #[Route(path: '/s4e4', name: 's4e4', methods: ['POST'])]
    public function index(Request $request)
    {
        $data = json_decode($request->getContent());
        file_put_contents(
            dirname(__DIR__,3).'/var/AiDev3_data/S4E4/payload.txt',
            '['.date('h:m:s').'] '.$data->instruction . PHP_EOL,
            FILE_APPEND
        );

//        $mapa = [
//            ['S', 'T', 'D', 'B'],
//            ['T', 'W', 'T', 'T'],
//            ['T', 'T', 'SK', 'DD'],
//            ['G', 'G', 'A', 'J'],
//        ];

        $map = [
            'S' => 'start',
            'T' => 'trawa',
            'D' => 'Drzewo',
            'W' => 'Wiatrak',
            'B' => 'Budynek',
            'SK' => 'Skały',
            'DD' => 'Drzewa',
            'G' => 'Góry',
            'A' => 'Auto',
            'J' => 'Jaskinia',
        ];

        $map = [
            'start' => ['(0,3)'],
            'trawa' => ['(0,1)', '(1,1)', '(0,2)', '(2,2)','(3,2)', '(1,3)'],
            'D' => ['(0,2)'],
            'Wiatrak' => ['(1,2)'],
            'Budynek' => ['(3,3)'],
            'Skały' => ['(2,1)'],
            'Drzewa' => ['(3,1)'],
            'Góry' => ['(0,0)', '(1,0)'],
            'Auto' => ['(2,0)'],
            'Jaskinia'  => ['(3,0)'],
        ];

        $prompt = file_get_contents(dirname(__DIR__, 3).'/Prompts/AiDev3/S4E4/prompt.txt');
        $user = 'Lecimy kolego teraz na sam dół mapy, a później ile tylko możemy polecimy w prawo. Teraz mała korekta o jedno pole do góry. Co my tam mamy?';

        $messages = [
            ['role' => 'system', 'content' => $prompt],
            ['role' => 'user', 'content' => $data->instruction]
        ];


        $assistant = $this->GPTservice->simplePrompt($messages, 'gpt-4o');
        foreach($map as $key => $value) {
            if(in_array(json_decode($assistant)->answer, $value)) {
                $response = $this->json(['description' => $key]);
            }
        }

        $response = $response ?? $this->json(['error' => 'Nie rozpoznano: ' . $assistant]);

        file_put_contents(
            dirname(__DIR__,3).'/var/AiDev3_data/S4E4/payload.txt',
            'api response:' . json_decode($response) . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );

        return $response;

    }
}