<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrelloController extends AbstractController
{

    #[Route('/trello', name: 'trello_board_list', methods: ['GET'])]
    public function getBoardList(
        ParameterBagInterface $ENV,
        HttpClientInterface $httpClient,
    ): Response
    {
        $key = $ENV->get('API_KEY_TRELLO');
        $token = $ENV->get('API_TOKEN_TRELLO');
        $endpoint = "https://api.trello.com/1/members/me/boards?key=$key&token=$token";

        $response = $httpClient->request('GET', $endpoint);
        $resParsed = json_decode($response->getContent(false));

        $data = array_map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'url' => $item->url,
            ];
        }, $resParsed);

        //dd($data);

        return $this->render('trello/list.html.twig', []);
    }

}