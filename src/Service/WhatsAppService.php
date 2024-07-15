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

    public function getQrCode()
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'http://localhost:3000/api/default/auth/qr?format=image',
                [
                    'headers' => [
                        'accept' => 'accept: image/png',
                        //'accept' => 'application/json',
                    ]
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
                "proxy" => null,
                "noweb" => [
                    "store" => [
                        "enabled" => true,
                        "fullSync" => false
                    ]
                ],
                "webhooks" => [
                    [
                        "url" => "https://webhook.site/11111111-1111-1111-1111-11111111",
                        "events" => [
                            "message",
                            "session.status"
                        ],
                        "hmac" => null,
                        "retries" => null,
                        "customHeaders" => null
                    ]
                ],
                "debug" => false
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
            $content = $response->getContent();
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
}
