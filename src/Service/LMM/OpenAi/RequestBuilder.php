<?php
declare(strict_types=1);

namespace App\Service\LMM\OpenAi;

use App\Entity\Conversation;
use Doctrine\Common\Collections\Collection;

class RequestBuilder
{
    private Request $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getResult(): Request
    {
        return $this->request;
    }

    public function setUrl(string $url): RequestBuilder
    {
        $this->request->url = $url;
        return $this;
    }

    public function setApiKey(string $apiKey): RequestBuilder
    {
        $this->request->apiKey = $apiKey;
        return $this;
    }

    public function setModel(string $model): RequestBuilder
    {
        $this->request->model = $model;
        return $this;
    }

    public function setSystemPrompt(string $systemPrompt): RequestBuilder
    {
        $this->request->systemPrompt = $systemPrompt;
        return $this;
    }

    public function setUserPrompt(string $userPrompt): RequestBuilder
    {
        $this->request->userPrompt = $userPrompt;
        return $this;
    }

    public function setConversation(Collection $messages): RequestBuilder
    {
        $this->request->conversation = array_map(function($message) {
            return [
                'role' => strtolower($message->getAuthor()) === 'user' ? 'user' : 'assistant',
                'content' => $message->getContent()
            ];
        }, $messages->toArray());
        return $this;
    }

    public function setImage(string $prompt, string $urlOrPath): RequestBuilder
    {
        $this->request->conversation = [
            'role' => 'user',
            'content' => [
                ['type' => 'text','text' => $prompt],
                ['type' => 'image_url', 'image_url' =>
                    filter_var($urlOrPath, FILTER_VALIDATE_URL) ?
                    ['url' => $urlOrPath] :
                    [ "url" => "data:image/jpeg;base64," . base64_encode(file_get_contents($urlOrPath))]
                ]
            ],
        ];
        return $this;
    }

    public function setTemperature(?float $temperature): RequestBuilder
    {
        $this->request->temperature = $temperature;
        return $this;
    }

    public function setMaxToken(?int $maxTokens): RequestBuilder
    {
        $this->request->maxTokens = $maxTokens;
        return $this;
    }

    public function setFunctionCalling(array $functionCalling): RequestBuilder
    {
        $this->request->functions = $functionCalling;
        return $this;
    }

    public function jsonMode(bool $jsonMode = false): RequestBuilder
    {
        $this->request->jsonMode = $jsonMode;
        return $this;
    }

    public function setStream(?bool $stream): RequestBuilder
    {
        $this->request->stream = $stream;
        return $this;
    }







}