<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class APIwhatsapp extends AbstractController
{

    #[Route('/api/whatsApp/{number}', 'whatsApp')]
    public function getMessages(Request $request, int $number): Response
    {
        // GET
        $endpoint = '/api/messages';

        $data = [
            'number' => $number,
            'name' => 'zbyszek',
            'lastname' => 'kowalski'
        ];


        return new JsonResponse($data);
    }
}
