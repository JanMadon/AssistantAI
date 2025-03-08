<?php

namespace App\Service\LMM\OpenAi;

use App\DTO\LMM\Prompt\PromptDto;
use App\DTO\LMM\Prompt\ResponseLmmDto;
use App\Entity\Conversation;
use App\Service\LMM\ChatClientServiceInterface;
use App\ValueObjects\LLM\ChatModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class OpenAiChatClientServiceService implements ChatClientServiceInterface
{

    const URL_OPENAI = [
        'standard' => 'https://api.openai.com/v1/chat/completions',
        'whisper' => 'https://api.openai.com/v1/audio/transcriptions',
        'speech' => 'https://api.openai.com/v1/audio/speech'
    ];

    private string $projectDir;


    public function __construct(
        private readonly  ParameterBagInterface $config,
        private readonly HttpClientInterface $httpClient,
        KernelInterface $kernel
    )
    {
        $this->projectDir = $kernel->getProjectDir();
    }

    public function functionCalling(PromptDto $prompt): ResponseLmmDto
    {
        $payload = [
            'model' => $prompt->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt->system_field
                ],
                [   'role' => 'user',
                    'content' => $prompt->content
                ]
            ],
            'functions' => $prompt->functions,
            'function_call' => 'auto',
        ];
        $responseLmm = $this->gptRequest($payload);
        dump($responseLmm);
        $response = new ResponseLmmDto(
            null,
            $responseLmm->id ?? 'error',
            $responseLmm->choices[0]->message->role ?? 'error',
            $responseLmm->choices[0]->message->content ?? 'empty',
            $responseLmm->model ?? 'error',
            $responseLmm->usage->prompt_tokens ?? 0,
            $responseLmm->usage->completion_tokens ?? 0,
            $responseLmm->usage->total_tokens?? 0,
        );
        $response->use_function = $responseLmm->choices[0]->message->function_call->name ?? null;
        $response->function_arguments = json_decode(
            $responseLmm->choices[0]->message->function_call->arguments,
            true)
            ?? [];

        return $response;


    }

    public function simplePrompt(array $messages, string $model = 'gpt-4o-mini')
    {
        $payload = [
            'model' => $model,
            'messages' => $messages
        ];

        return $this->gptRequest($payload);
    }

    public function oneShootPrompt(string $system, string $prompt, string $model = 'gpt-4o-mini', bool $jsonMode = false): string
    {
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ]
        ];
        if($jsonMode){
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return $this->gptRequest($payload);
    }

    public function prompt(Conversation $conversation): ResponseLmmDto
    {
        $request = (new RequestBuilder())
            ->setUrl(self::URL_OPENAI['standard'])
            ->setApiKey($this->config->get('API_KEY_OPENAI'))
            ->setModel($conversation->getModelId())
            ->setSystemPrompt($conversation->getSystemField())
            ->setConversation($conversation->getMessages())
            ->setTemperature($conversation->getTemperature())
            ->setMaxToken($conversation->getMaxToken())
            ->setStream(false)
            ->getResult();

        $res = $request->makeRequest();
        if($res->responseStatus === Request::ERROR){
            throw new \Exception($res->errorMessages);
        }

        return new ResponseLmmDto(
            $conversation,
            $res->getResponseId(),
            $res->getRole(),
            $res->getStandardContent(),
            $res->getModelName(),
            $res->getUsedTokens()['prompt'],
            $res->getUsedTokens()['completion'],
            $res->getUsedTokens()['total'],
        );

    }

    public function promptVisionModelWithUrlImage(promptDto $promptDto): ResponseLmmDto
    {
        $payload = [
            'model' => $promptDto->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $promptDto->content
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => ['url' => $promptDto->function_arguments['url']]
                        ]
                    ]
                ]
            ]
        ];
        $responseLmm = $this->gptRequest($payload);
        return new ResponseLmmDto(
            null,
            $responseLmm->id ?? 'error',
            $responseLmm->choices[0]->message->role ?? 'error',
            $responseLmm->choices[0]->message->content ?? 'empty',
            $responseLmm->model ?? 'error',
            $responseLmm->usage->prompt_tokens ?? 0,
            $responseLmm->usage->completion_tokens ?? 0,
            $responseLmm->usage->total_tokens?? 0,
        );
    }

    public function promptVisionModel(array $messages, string $model = 'gpt-4o-mini')
    {
        $payload = [
            'model' => $model,
            'messages' => $messages
        ];

        return $this->gptRequest($payload);
    }

    public function promptImage(string $prompt, string $imagePath, string $model = 'gpt-4o-mini')
    {
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            "type" => "text",
                            "text" => $prompt
                        ],
                        [
                            "type" => "image_url",
                            "image_url" => [ "url" => "data:image/jpeg;base64," . base64_encode(file_get_contents($imagePath)) ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->gptRequest($payload);
    }

    public function imageGeneration($prompt): string
    {
        $request = (new RequestBuilder())
            ->setSystemPrompt($prompt)
            ->getResult();

        $res = $request->makeDallE3Request();

        return $res->getUrlResult();
    }


    function Embedding($input): array
    {
        $request = (new RequestBuilder())
            ->setModel('text-embedding-ada-002')
            ->setSystemPrompt($input)
            ->getResult();

        $res = $request->makeEmbeddings();
        if($res->responseStatus === Request::ERROR){
            throw new \Exception($res->errorMessages);
        }

        return $res->getEmbeddingResult();
    }

    public function getChatModels(): array
    {
        $res = (new Request())->models();

        return $res->getModelsResult();
    }


    public function createSpeech(string $text, string $savePath): ?string
    {
        $filesystem = new Filesystem();

        try{
            $response = $this->httpClient->request(
                'POST',
                self::URL_OPENAI['speech'],
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI'),
                        'Content-Type' => ' application/json'
                    ],
                    'json' => [
                        'model' => 'tts-1',
                        'input' => $text,
                        'voice' => 'alloy'
                    ]
                ]

            );
            $speech = $response->getContent(false);
            $directory = pathinfo($savePath, PATHINFO_DIRNAME);

            if (!$filesystem->exists($directory)) { // create dir if not exist
                $filesystem->mkdir($directory, 0755);
            }

            file_put_contents($savePath, $speech);

            return $savePath;

        } catch (\Throwable $exception) {
            $response = $exception->getMessage();
        }
        return $response;
    }


    /**
     * DOC OPEN_AI: https://platform.openai.com/docs/api-reference/audio/createTranscription
     * @param string $filePath
     * @return string|null
     */
    public function makeTranscription(string $filePath): ?string
    {
        try{
            $response = $this->httpClient->request(
                'POST',
                self::URL_OPENAI['whisper'],
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI'),
                        'Content-Type' => ' multipart/form-data'
                    ],
                    'body' => [
                        'file' => fopen($filePath, 'r'),
                        'model' => 'whisper-1'
                    ]
                ]

            );
            $responseJson = $response->getContent(false);
            return json_decode($responseJson)->text;
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $response = $e->getMessage();
        }
        return $response;
    }


    /**
     * @param array $payload
     * @return object|string
     * @throws TransportExceptionInterface
     */
    private function gptRequest(array $payload): object|string
    {
        try {
            $request = $this->httpClient->request(
                'POST',
                $this->url,
                [
                    'json' => $payload,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Content-Length: ' . strlen(json_encode($payload)),
                        'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI')
                    ]
                ]
            );

            return json_decode($request->getContent(false));

        } catch (ClientException $exception) {
            $response = $exception->getMessage();
        } catch (HttpExceptionInterface $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = 'Unexpected error: ' . $exception->getMessage();
        }

        return $response;
    }

}

