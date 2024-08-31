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
    private string $sessionName = 'default';
    private WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    #[Route('/whatsApp/home', name: 'whatsApp.home', methods: ['GET'])]
    public function home(Request $request): Response
    {
        $action = $request->query->get('action');

        $session = $this->whatsAppService->getSession()[0] ?? null; // $session->name

        if($session){
            switch ($action) {
                // SESSION
                case 'start':
                    $startSession = $this->whatsAppService->startSession();
                    break;
                case 'get_qrCode':
                    $qrCode = $this->whatsAppService->getQrCode();
                    break;
                case 'stop':
                    $stopSession = $this->whatsAppService->stopSession() === null ?
                        'stopped': $this->whatsAppService->stopSession();
                    break;

                // Action for session
                case 'get_chats':
                    $chats = $this->whatsAppService->getChats($session['name']);
                    break;
                case 'get_contacts':
                    $contacts = $this->whatsAppService->getContacts($session['name']);
                    dump($contacts);
                    break;
            }
        }

        return $this->render('whatsApp/home.html.twig', [
            'session' => $session ?? null,
            'qrCode' => $qrCode ?? null,
            'startSession' => $startSession ?? null,
            'stopSession' => $stopSession ?? null,
            'chats' => $chats ?? null,
            'contacts' => $contacts ?? null,
        ]);
    }

    #[Route('/whatsApp/chats/{id}', name: 'whatsApp.chats', methods: ['GET'])]
    public function chats(Request $request, $id): Response
    {

        $chats = $this->whatsAppService->getChats($this->sessionName);

        if($request->isMethod('POST') ) {
            // todo validacja
            $message = $request->get('message');
            $result = $this->whatsAppService->sendMessage($this->sessionName, $id,$message);
            var_dump($result);
        }

        $rowMessages = $this->whatsAppService->getMessages($this->sessionName, $id);
      //  dd($rowMessages);
        $messages = [];
        $ackMap = [
            'wysłana do serwera',
            'dostarczona do odbiorcy',
            'wiadomość nie mogła zostać dostarczona',
            'przeczytana przez odbiorcę',
        ];

        foreach ($rowMessages as $rowMessage) {
            $time = (new \DateTime("@$rowMessage->timestamp"))->format('Y-m-d H:i:s');
            $messages[] = [
                'id' => $rowMessage->id,
                'fromMe' => $rowMessage->fromMe,
                'from' => $rowMessage->from,
                'to' => $rowMessage->to,
                'body' => $rowMessage->body,
                'time' => $time,
                'ack' => ucfirst($ackMap[$rowMessage->ack]) . " - $rowMessage->ackName (#$rowMessage->ack) "
            ];
        }

        return $this->render('whatsApp/chat.html.twig', [
            'chats' => $chats,
            'id' => $id,
            'messages' => $messages,
        ]);
    }

    #[Route('/whatsApp/chats/{id}', name: 'whatsApp.chats.message', methods: ['POST'])]
    public function messages(Request $request, $id): Response
    {
        $message = $request->get('message');
        $result = $this->whatsAppService->sendMessage($this->sessionName, $id,$message);
        return new JsonResponse($result);
    }

    // {session} controllers for api returns JSON:
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
