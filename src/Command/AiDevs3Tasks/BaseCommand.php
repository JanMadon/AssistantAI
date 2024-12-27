<?php

namespace App\Command\AiDevs3Tasks;

use App\Service\Aidev3\AiDev3PreWorkService;
use App\Service\GPTservice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class BaseCommand extends Command
{
    protected GPTservice $GPTservice;
    protected AiDev3PreWorkService $aiDev3PreWorkService;
    protected array $aiDevs3Endpoint;
    protected HttpClientInterface $httpClient;
    protected ParameterBagInterface $envParma;

    public function __construct(
        GPTservice $GPTservice,
        AiDev3PreWorkService $aiDev3PreWorkService,
        ParameterBagInterface $parameterBag,
        HttpClientInterface $httpClient,
        CacheInterface $cache
    )
    {
        parent::__construct();

        $this->GPTservice = $GPTservice;
        $this->aiDev3PreWorkService = $aiDev3PreWorkService;
        $this->aiDevs3Endpoint = $parameterBag->get('AI3_ENDPOINTS');
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->envParma = $parameterBag;
    }
}
