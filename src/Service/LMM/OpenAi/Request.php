<?php

namespace App\Service\LMM\OpenAi;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Request
{
    public const SUCCESS = 'success';
    public const ERROR = 'error';

    public string $url;
    public string $apiKey;
    public string $model;
    public string $systemPrompt;
    public array $conversation;
    public ?float $temperature;
    public ?int $maxTokens;
    public ?bool $stream;

    public string $responseStatus;
    public string $errorMessages;
    public object $result;



    public function makeRequest(): self
    {
        $httpClient = HttpClient::create();
        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ...$this->conversation,
            ],
        ];

        if($this->temperature){
            $payload['temperature'] = $this->temperature;
        }
        if($this->maxTokens){
            $payload['max_tokens'] = $this->maxTokens;
        }
        if($this->stream){
            $payload['stream'] = $this->stream;
        }

        try {
            $request = $httpClient->request(
                'POST',
                $this->url,
                [
                    'json' => $payload,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Content-Length: ' . strlen(json_encode($payload)),
                        'Authorization' => 'Bearer ' . $this->apiKey
                    ]
                ]
            );

            $this->result = json_decode($request->getContent(false));
            $this->responseStatus = self::SUCCESS;
            return $this;

        } catch (ClientException $exception) {
            $response = $exception->getMessage();
        } catch (HttpExceptionInterface $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = 'Unexpected error: ' . $exception->getMessage();
        } catch (TransportExceptionInterface $e) {
            $response = $e->getMessage();
        }

        $this->responseStatus = self::ERROR;
        $this->errorMessages = $response;
        return $this;
    }

    public function getStandardContent():string
    {
        return $this->result->choices[0]->message->content ?? 'empty';
    }

    public function getResponseId():string
    {
        return $this->result->id ?? 'empty';
    }

    public function getRole():string
    {
        return $this->result->choices[0]->message->role ?? 'empty';
    }

    public function getModelName():string
    {
        return $this->result->model ?? 'empty';
    }

    public function getUsedTokens(): array
    {
        return [
            'prompt' => $this->result->usage->prompt_tokens ?? 0,
            'completion' => $this->result->usage->completion_tokens ?? 0,
            'total' => $this->result->usage->total_tokens ?? 0,
        ];
    }
}