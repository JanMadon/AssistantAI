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

    private string $url;
    private ParameterBagInterface $config;
    private HttpClientInterface $httpClient;

    public function __construct(ParameterBagInterface $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->url = 'http://localhost:3000/api/';
    }
    
    public function getContacts($name)
    {
        $endpoint = "contacts/all?session=$name";
        $result = $this->makeRequest('GET', $endpoint, [] );
        return json_decode($result, true);
    }

    // getChats pobiera tylko 1 widaomość z karzdego chatu
    public function getChats($sessionName)
    {
        $endpoint = "$sessionName/chats";
        $result = $this->makeRequest('GET', $endpoint, [] );
        return json_decode($result, true);
    }

    public function getMessages($sessionName, $chatNumber)
    {
        $endpoint = "$sessionName/chats/$chatNumber/messages"; //?downloadMedia=true&limit=100'
        $result = $this->makeRequest('GET', $endpoint, [] );
        return json_decode($result);
    }
    
    public function getQrCode(): string
    {
        $header = ['accept' => 'accept: image/png'];
        $image = $this->makeRequest('GET', 'default/auth/qr?format=image', $header);
        return base64_encode($image);
    }

    public function getSession()
    {
        $endpoint = "sessions?all=true";
        $result = $this->makeRequest('GET', $endpoint, [] );
        return json_decode($result);
    }

    public function startSession()
    {
        $endpoint = "sessions/start";
        $header = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
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

        $result = $this->makeRequest('POST', $endpoint, $header, $body);
        return json_decode($result, true);
    }

    public function stopSession()
    {
        $body = ['name' => 'default'];
        $header = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        $endpoint = "sessions/stop";

        $result = $this->makeRequest('POST', $endpoint, $header, $body);

        return json_decode($result, true);
    }

    public function logoutSession()
    {
        $body = ['name' => 'default'];
        $header = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        $endpoint = "sessions/logout";
        $result = $this->makeRequest('POST', $endpoint, $header, $body);

        return json_decode($result, true);
    }

    public function sendMessage($sessionName, $chatId, $body)
    {
        $body = [
            'chatId' => $chatId,
            'text' =>  $body,
            'session' => $sessionName
        ];
        $header = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        $endpoint = "sendText";

        $result = $this->makeRequest('POST', $endpoint, $header, $body);

        return json_decode($result, true);
    }

    private function makeRequest(string $method, string $endpoint, array $headers, ?array $body = null,)
    {
        try {
            $response = $this->httpClient->request(
                $method,
                $this->url . $endpoint,
                [
                    'headers' => $headers,
                    'body' => json_encode($body),
                ]
            );
            $content = $response->getContent();
        } catch (HttpExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            $content = $e->getResponse()->getContent();
        } catch (TransportExceptionInterface $e) {
            $content = $e->getMessage();
        }
/*
* ClientExceptionInterface -> Dotyczy błędów po stronie klienta (4xx), np. 404 (Not Found) czy 400 (Bad Request).
* RedirectionExceptionInterface -> Dotyczy przekierowań (3xx), np. 301 (Moved Permanently) czy 302 (Found).
* ServerExceptionInterface -> Dotyczy błędów po stronie serwera (5xx), np. 500 (Internal Server Error) czy 503 (Service Unavailable).
* HttpExceptionInterface -> Ogólny wyjątek HTTP, który obejmuje wszystkie powyższe sytuacje związane z odpowiedzią HTTP, bez względu na konkretny typ błędu (klient, serwer, przekierowanie).
* TransportExceptionInterface -> Najbardziej ogólny, dotyczy problemów z transportem, np. braku połączenia, timeoutów, problemów sieciowych, niezależnie od odpowiedzi HTTP.
*/
        return $content;
    }
}
