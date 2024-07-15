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

    #[Route('/api/whatsApp/session/qr')]
    public function getQR(Request $request): Response
    {
        $response = new Response($this->whatsAppService->getQrCode());
        $response->headers->set('Content-Type', 'image/png');
        return $response;
    }

    #[Route('/api/whatsApp/session')]
    public function getSession(Request $request): Response
    {
        return new JsonResponse($this->whatsAppService->getSession());
    }

    #[Route('/api/whatsApp/session/start')]
    public function sessionStart(): Response
    {
        return new JsonResponse($this->whatsAppService->startSession());
    }

    #[Route('/api/whatsApp/session/stop')]
    public function sessionStop(): Response
    {
        return new JsonResponse($this->whatsAppService->stopSession());
    }
}
