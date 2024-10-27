<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrelloController extends AbstractController
{
    private string $key;
    private string $token;

    public function __construct(
        private HttpClientInterface $httpClient,
        private ParameterBagInterface $ENV
    ) {
        $this->key = $this->ENV->get('API_KEY_TRELLO');
        $this->token = $this->ENV->get('API_TOKEN_TRELLO');
    }

    #[Route('/trello/boards', name: 'trello_board_list', methods: ['GET'])]
    public function getBoardList(): Response
    {
        $endpoint = "https://api.trello.com/1/members/me/boards?key=$this->key&token=$this->token";

        $response = $this->httpClient->request('GET', $endpoint);
        $resParsed = json_decode($response->getContent(false));

        $data = array_map(function ($item) {
            return [
                'id' => $item->id,
                'shortId' => $item->shortLink,
                'name' => $item->name,
                'url' => $item->url,
            ];
        }, $resParsed);

        return $this->render('trello/board_list.html.twig', [
            'boards' => $data,
        ]);
    }

    #[Route('/trello/boards/{id}', name: 'trello_board_show', methods: ['GET'])]
    public function getBoard(string $id)//: Response
    {
        $endpoint = "https://api.trello.com/1/boards/$id/cards?key=$this->key&token=$this->token";
        //dd($endpoint);
        $response = $this->httpClient->request('GET', $endpoint);
        $resParsed = json_decode($response->getContent(false));
        dd($resParsed);

//        $data = array_map(function ($item) {
//            return [
//                'id' => $item->id,
//                'shortId' => $item->shortLink,
//                'name' => $item->name,
//                'url' => $item->url,
//            ];
//        }, $resParsed);

        //return $this->render('trello/board_selected.html.twig', []);
    }

}