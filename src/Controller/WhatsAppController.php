<?php

namespace App\Controller;

use App\Service\WhatsAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WhatsAppController extends AbstractController
{
    private WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    #[Route('/api/whatsApp/{number}', 'whatsApp')]
    public function getMessages(Request $request, int $number): Response
    {
        // sprawdz sesje:
        

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
