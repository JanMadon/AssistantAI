<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WikipediaController extends AbstractController
{
    private string $token;

    public function __construct(
        private HttpClientInterface $httpClient,
        private ParameterBagInterface $ENV
    ) {
        $this->token = $this->ENV->get('WIKI_API_TOKEN');
    }

    /*

    #[Route('/wikipedia/get-today-article', name: 'wikipedia_today_article', methods: ['GET'])]
    public function getBoardList()
    {

    }
    */

}