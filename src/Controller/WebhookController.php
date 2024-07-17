<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController extends AbstractController
{

    #[Route('/webhook/whatsApp/message', name: 'webhook')]
    public function receiveMessageWebhook(Request $request): Response
    {
        $content = $request->getContent();

        file_put_contents(__DIR__.'/test.json', $content);

        return new Response('ok');
    }
}