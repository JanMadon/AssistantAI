<?php

namespace App\Service\LMM\OpenAi;

use App\Event\StreamDataEvent;
use App\ValueObjects\LLM\ChatModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Request
{
    const URL_OPENAI = [
        'standard' => 'https://api.openai.com/v1/chat/completions',
        'whisper' => 'https://api.openai.com/v1/audio/transcriptions',
        'speech' => 'https://api.openai.com/v1/audio/speech',
        'dall-e-3' => 'https://api.openai.com/v1/images/generations',
        'embeddings' => 'https://api.openai.com/v1/embeddings',
        'models' => 'https://api.openai.com/v1/models'
    ];
    public const SUCCESS = 'success';
    public const ERROR = 'error';

    public string $url;
    public string $apiKey;
    public string $model;
    public string $systemPrompt;
    public string $userPrompt;
    public array $conversation;
    public ?float $temperature;
    public ?int $maxTokens;
    public ?bool $stream;
    public string $responseStatus;
    public string $errorMessages;
    public object $result;
    public array $functions;
    public bool $jsonMode;


    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->apiKey = $parameterBag->get('API_KEY_OPENAI');
    }

    public function makeGptRequest(): self
    {
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
        if($this->jsonMode){
            $payload['response_format'] = ['type' => 'json_object'];
        }
        if($this->stream){
            $payload['stream'] = $this->stream;
        }
        $this->request(self::URL_OPENAI['standard'], $payload);
        return $this;
    }

    public function makeFunctionCallingRequest(): self
    {
        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user', 'content' => $this->userPrompt],
            ],
            'function_calling' => $this->functions,
            'function_call' => 'auto',
        ];
        $this->request(self::URL_OPENAI['standard'], $payload);

        return $this;
    }

    public function makeDallE3Request(): self
    {
        $payload = [
            'prompt' => $this->systemPrompt,
            'n' => 1,
            'size' => '1024x1024', // 1024x1024, 1024x1792 or 1792x1024
            'quality' => 'standard',
            'response_format' => 'url'
        ];
        $this->request(self::URL_OPENAI['dall-e-3'], $payload);
        return $this;
    }

    public function makeEmbeddings(): self
    {
        $payload = [
            'model' => $this->model,
            'encoding_format' => 'float',
            'input' => $this->systemPrompt,
        ];
        $this->request(self::URL_OPENAI['embeddings'], $payload);
        return $this;
    }

    public function models(): self
    {
        $this->request(self::URL_OPENAI['models']);
        return $this;
    }

    public function getStandardResult():string
    {
        return $this->result->choices[0]->message->content ?? 'empty';
    }

    public function getUrlResult(): string // for dall-e-3
    {
        return $this->result->data[0]->url ?? 'empty';
    }

    public function getEmbeddingResult(): array
    {
        return $this->result->data[0]->embedding ?? [];
    }

    public function getModelsResult(): array
    {
        return array_map(fn($model) => new ChatModel($model->id, $model->id), $this->result->data) ?? [];
    }
    public function getFunction(): string
    {
        return $this->result->choices[0]->message->function_call->name ?? 'empty';
    }

    public function getFunctionArguments(): array
    {
        return json_decode($responseLmm->choices[0]->message->function_call->arguments,true) ?? [];
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

    private function request(string $url, array $payload = []): void
    {
        $httpClient = HttpClient::create();
        $method = 'GET';
        $body = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ]
        ];
        if($payload){
            $method = 'POST';
            $body['json'] = json_encode($payload);
            $body['headers']['Content-Type'] = 'application/json';
        }

        try {
            $request = $httpClient->request($method,$url,$body);

            $this->result = json_decode($request->getContent(false));
            $this->responseStatus = self::SUCCESS;
            return;

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
    }

    private function streamRequest(string $method, string $url, array $body): void
    {
        $request = $this->httpClient->request($method, $url, $body);

        foreach ($this->httpClient->stream($request) as $chunk) {
            if ($chunk->isTimeout() === false && $chunk->isFirst() === true) {
                //dump("Strumień rozpoczęty");
            }

            if ($chunk->isLast()) {
                //dd("Strumień zakończony");
            }

            // Przetwarzanie zawartości
            $data = $chunk->getContent();
            $subChunks = explode("\n\n", trim($data));
            foreach ($subChunks as $subChunk) {
                if (str_starts_with($subChunk, 'data: ')) {
                    $jsonSubChunk = substr($subChunk, 6);
                    $decodedData = json_decode($jsonSubChunk);
                    if(isset($decodedData->choices[0]->delta->content)) {
                       $this->eventDispatcher->dispatch(new StreamDataEvent(
                           $decodedData->choices[0]->delta->content,
                       ), 'stream.data');
                        // $this->result = yield $decodedData?->choices[0]?->delta?->content;
                    }
                }
            }
        }
    }

}