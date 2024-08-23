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

    #[Route('/whatsApp/home', name: 'whatsApp.home', methods: ['GET'])]
    public function home(Request $request): Response
    {
        $action = $request->query->get('action');
        switch ($action) {
            case 'start':
                $startSession = $this->whatsAppService->startSession();
                break;
            case 'get_qrCode':
                $qrCode = $this->whatsAppService->getQrCode();
                break;
            case 'get_session':
                $session = $this->whatsAppService->getSession();
                break;
            case 'stop':
                $stopSession = $this->whatsAppService->stopSession();
                break;
        }
        dd($stopSession);

        return $this->render('whatsApp/home.html.twig',[
            'session' => $session ?? null,
            'qrCode' => $qrCode ?? null,
            'startSession' => $startSession ?? null,
            'stopSession' => $stopSession ?? null,
        ]);
    }

    #[Route('/api/whatsApp/session/qr', name: 'whatsApp.session.qr', methods: ['GET'])]
    public function getQR(Request $request): Response
    {
        $response = new Response($this->whatsAppService->getQrCode());
        $response->headers->set('Content-Type', 'image/png');
        return $response;
    }

    #[Route('/api/whatsApp/session', name: 'whatsApp.session.get', methods: ['GET'])]
    public function getSession(Request $request): Response
    {
        return new JsonResponse($this->whatsAppService->getSession());
    }

    #[Route('/api/whatsApp/session/start', name: 'whatsApp.session.start', methods: ['GET'])]
    public function sessionStart(): Response
    {
        return new JsonResponse($this->whatsAppService->startSession());
    }

    #[Route('/api/whatsApp/session/stop', name: 'whatsApp.session.stop', methods: ['GET'])]
    public function sessionStop(): Response
    {
        return new JsonResponse($this->whatsAppService->stopSession());
    }
}
