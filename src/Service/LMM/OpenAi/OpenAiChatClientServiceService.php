<?php

namespace App\Service\LMM\OpenAi;

use App\DTO\LMM\Prompt\PromptDto;
use App\DTO\LMM\Prompt\ResponseLmmDto;
use App\Entity\Conversation;
use App\Service\LMM\ChatClientServiceInterface;
use App\ValueObjects\LLM\ChatModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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

    private string $url = 'https://api.openai.com/v1/chat/completions';
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
        $payload = [
            'model' => $conversation->getModelId(),
            'messages' => [[
                    'role' => 'system',
                    'content' => $conversation->getSystemField(),
                ], ...array_map(function($message) {
                return [
                    'role' => strtolower($message->getAuthor()) === 'user' ? 'user' : 'assistant',
                    'content' => $message->getContent()
                ];
            }, $conversation->getMessages()->toArray())]
        ];

        $response = $this->gptRequest($payload);

        return new ResponseLmmDto(
            $conversation,
            $response->id ?? 'error',
            $response->choices[0]->message->role ?? 'error',
            $response->choices[0]->message->content ?? 'error',
            $response->model ?? 'error',
            $response->usage->prompt_tokens ?? 0,
            $response->usage->completion_tokens ?? 0,
            $response->usage->total_tokens?? 0,
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

    public function makeTranscription(string $filePath): string
    {
        try{
            $response = $this->httpClient->request(
                'POST',
                'https://api.openai.com/v1/audio/transcriptions',
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
            $response = $response->getContent(false);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $response = $e->getMessage();
        }
        return $response;
    }



    public function imageGeneration($prompt)
    {
        $payload = [
            'model'=>'dall-e-3',
            'prompt'=>$prompt,
            'n' => 1,
            'size' => '1024x1024' // 1024x1024, 1024x1792 or 1792x1024
        ];

        try{
            $response = $this->httpClient->request(
                'POST',
                'https://api.openai.com/v1/images/generations',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI'),
                        'Content-Type' => 'application/json'
                    ],
                    'json' => $payload
                ]

            );
            $response = $response->getContent(false);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $response = $e->getMessage();
        }
        return json_decode($response)->data[0]->url;
    }



    function makeEmbeding($input): array 
    {
        $payload = [
            'model' => 'text-embedding-ada-002', // vectors size: 1536 
            'encoding_format' => 'float',
            'input' => json_encode($input)
        ];

        try {

            $request = $this->httpClient->request(
                'POST',
                'https://api.openai.com/v1/embeddings',
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

            $response = json_decode($request->getContent(false))->data[0]->embedding;;

        } catch (ClientException $exception) {
            $response = $exception->getMessage();
        }  catch (HttpExceptionInterface $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = 'Unexpected error: ' . $exception->getMessage();
        } 

        return (array) $response;
    }

    public function getChatModels(): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'https://api.openai.com/v1/models',
                [
                   'headers' => [
                       'Accept' => 'application/json',
                       'Authorization' => 'Bearer ' . $this->config->get('API_KEY_OPENAI')
                   ]
                ]);
            $response = json_decode($response->getContent(false));
        } catch (ClientException $exception) {
            $response = $exception->getMessage();
        }  catch (HttpExceptionInterface $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = 'Unexpected error: ' . $exception->getMessage();
        } catch (TransportExceptionInterface $e) {
        }

        if(isset($response->data)){
            return array_map(fn($model) => new ChatModel($model->id, $model->id), $response->data);
        }

        return [];
    }

    private function prepareConversationArray(array|string $conversation): array
    {
        if(is_string($conversation)){
            return [[
                'role'=>'user',
                'content'=> $conversation
            ]];
        }

        $preparedConversation = [];
        foreach ($conversation as $messages) {

            if (property_exists($messages, 'User')) {
                $preparedConversation[] = [
                    'role' => 'user',
                    'content' => $messages->User
                ];
            }
            if (property_exists($messages, 'AI')) {
                $preparedConversation[] = [
                    'role' => 'assistant',
                    'content' => $messages->AI
                ];
            }
        }

        return $preparedConversation;
    }

    /**
     * @param array $payload
     * @return object
     * @throws TransportExceptionInterface
     */
    public function gptRequest(array $payload)
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

