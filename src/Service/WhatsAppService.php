<?php

namespace App\Service;

use App\Service\Interface\WhatsAppServiceInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WhatsAppService implements WhatsAppServiceInterface
{

    private ParameterBagInterface $config;
    private HttpClientInterface $httpClient;

    public function __construct(ParameterBagInterface $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }
    
    public function getContacts($name)
    {
        try{
            $response = $this->httpClient->request(
                'GET',
                'http://localhost:3000/api/contacts/all?session='. $name
            );
            $content = $response->getContent(false);

        } catch(HttpExceptionInterface $e) {
            //$content = $e->getResponse()->getContent();
            dd($e->getMessage());
        }

        return json_decode($content, true);
    }

    // getChats pobiera tylko 1 widaomość z karzdego chatu
    public function getChats($sessionName)
    {
        try{
            $response = $this->httpClient->request(
                'GET',
                'http://localhost:3000/api/' . $sessionName . '/chats',
            );
            $content = $response->getContent(false);

        } catch(HttpExceptionInterface $e) {
            $content = $e->getResponse()->getContent();
        }

        return json_decode($content, true);
    }

    public function getMessages($sessionName, $chatNumber)
    {
        try{
            $response = $this->httpClient->request(
                'GET',
                'http://localhost:3000/api/'.$sessionName.'/chats/'.$chatNumber.'/messages', //?downloadMedia=true&limit=100'
            );
            $content = $response->getContent(false);

        } catch(HttpExceptionInterface $e) {
            $content = $e->getResponse()->getContent();
        }

        return json_decode($content);
    }
    
    public function getQrCode()
    {
        $header = ['accept' => 'accept: image/png'];
        $image = $this->makeRequest('GET', 'default/auth/qr?format=image', $header);
        return base64_encode($image);
    }

    public function getSession()
    {
        try{
            $response = $this->httpClient->request(
                'GET',
                'http://localhost:3000/api/sessions?all=true',
            );

            $content = $response->getContent();

        } catch(HttpExceptionInterface $e) {
            $content = $e->getResponse()->getContent();
        }
        return json_decode($content, true);
    }

    public function startSession()
    {
        $body = [
            "name" => "default",
            "config" => [
               // "proxy" => null,
                "noweb" => [
                    "store" => [
                        "enabled" => true,
                        "fullSync" => false
                    ]
                ],
                "webhooks" => [
                    [
                        "url" => 'http://localhost/webhook.php', //"https://localhost:8080/webhook/whatsApp/message",
                        "events" => [
                            "message",
                        ]
                    ]
                ],
                "debug" => true
            ]
        ];        

        try {
            $response = $this->httpClient->request(
                'POST',
                'http://localhost:3000/api/sessions/start',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'body' => json_encode($body),
                ]
            );            
            $content = $response->getContent(false);
        }  catch (HttpExceptionInterface $e) { 
            $content = $e->getResponse()->getContent(false);
        }

        return json_decode($content, true);
    }

    public function stopSession()
    {
        $body = ['name' => 'default'];  

        try {
            $response = $this->httpClient->request(
                'POST',
                'http://localhost:3000/api/sessions/stop',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'body' => json_encode($body),
                ]
            );            
            $content = $response->getContent();
        }  catch (HttpExceptionInterface $e) { 
            $content = $e->getResponse()->getContent(false);
        }

        return json_decode($content, true);
    }

    public function logoutSession()
    {
        $body = ['name' => 'default'];

        try {
            $response = $this->httpClient->request(
                'POST',
                'http://localhost:3000/api/sessions/logout',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'body' => json_encode($body),
                ]
            );
            $content = $response->getContent();
        }  catch (HttpExceptionInterface $e) {
            $content = $e->getResponse()->getContent(false);
        }

        return json_decode($content, true);
    }

    public function sendMessage($sessionName, $chatId, $body)
    {
        $payload = [
            'chatId' => $chatId,
            'text' =>  $body,
            'session' => $sessionName
        ];

        try {
            $response = $this->httpClient->request(
                'POST',
                'http://localhost:3000/api/sendText',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'body' => json_encode($payload),
                ]
            );
            $content = $response->getContent();

        } catch (HttpExceptionInterface $e) {
            $content = $e->getMessage();
        }
        return json_decode($content, true);
    }

    private function makeRequest(string $method, string $endpoint, array $headers, ?array $body = null,)
    {
        try {
            $response = $this->httpClient->request(
                $method,
                'http://localhost:3000/api/' . $endpoint,
                [
                    'headers' => $headers,
                    'body' => json_encode($body),
                ]
            );
            $content = $response->getContent();
        } catch (ClientExceptionInterface $e) {
            $content = $e->getResponse()->getContent(false);
        } catch (ServerExceptionInterface $e) {
            $content = $e->getResponse()->getContent(false);
        } catch (RedirectionExceptionInterface $e) {
            $content = $e->getResponse()->getContent(false);
        } catch (TransportExceptionInterface $e) {
            dd($e->getMessage());
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        return $content;
    }
}
