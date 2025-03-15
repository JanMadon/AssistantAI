<?php
namespace App\Controller;

use App\DTO\LMM\Prompt\PromptDto;
use App\DTO\LMM\SettingLmmDto;
use App\DTO\LMM\TemplateLmmDto;
use App\Entity\Stream;
use App\Repository\ConversationRepository;
use App\Repository\LMM\SettingsLmmRepository;
use App\Repository\StreamRepository;
use App\Repository\TemplateRepository;
use App\Service\Assistant\ChatService;
use App\Service\LMM\OpenAi\OpenAiChatClientServiceService;
use App\Service\Validators\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;


class AssistantController extends AbstractController
{
    public function __construct(
        private readonly OpenAiChatClientServiceService $gptService,
        private readonly ConversationRepository         $conversationRepository,
        private readonly TemplateRepository             $templateRepository,
        private readonly SettingsLmmRepository          $settingsLmmRepository,
        private readonly ValidatorService               $validatorService,
        private readonly ChatService                    $chatService,
    ){}

    #[Route('/assistant', 'assistant', methods: ['GET'])]
    public function assistant(Request $request, SerializerInterface $serializer): Response
    {

        $conversations = $this->conversationRepository->findBy([], ['id' => 'DESC']);
        $conversationsJson = $serializer->serialize($conversations, 'json', ['groups' => 'conversation']);
        return $this->render('assistant/main.html.twig', [
            'conversations' => $conversations,
            'conversationsJson' => $conversationsJson,
            'templates' => $this->templateRepository->findBy([], ['id' => 'DESC']),
            'models' => $this->gptService->getChatModels(),
            'lmmSettings' => $this->settingsLmmRepository->findAll(),
        ]);
    }

    #[Route('/api/assistant', 'assistant_data', methods: ['POST'])]
    public function assistantData(Request $request, SerializerInterface $serializer): Response
    {
        $templatePage = $request->get('template_page', 1);
        $templatePerPage = $request->get('template_per_page', 10);

        $lastConversationPage = $request->get('last_conv_page', 1);
        $lastConversationPerPage = $request->get('last_conv_limit', 10);

       return new JsonResponse([

       ]);
    }

    #[Route('/api/assistant/prompt', 'assistant_prompt', methods:['POST'])]
    public function assistantConversation(Request $request): Response
    {
        $requestData = json_decode($request->getContent());

        $promptDto = new PromptDto(
            $requestData->id,
            $requestData->system,
            $requestData->message->role,
            $requestData->message->content,
            $requestData->model,
            $requestData->config->temperature,
            $requestData->config->max_token,
            $requestData->config->function_calling,
            $requestData->additional->file_name ?? null,
            $requestData->config->stream ?? false,
        );
        $this->validatorService->validateAndThrow($promptDto);

        $responseChat = $this->chatService->chat($promptDto);

        return new JsonResponse([
            'answer' => $responseChat->content,
            'id' => $responseChat->forConversation->getId(),
            'usage_tokens' => [
                'prompt' => $responseChat->prompt_tokens,
                'complication' => $responseChat->completion_tokens,
                'total' => $responseChat->total_tokens
            ]
        ], 200);
    }

    #[Route('/api/assistant/prompt/sse', 'assistant_stream', methods:['GET'])]
    public function assistantStreamResponse(EntityManagerInterface $entityManager): StreamedResponse
    {
        return new StreamedResponse(function () use($entityManager) {

            /** @var StreamRepository $streamRepository */
            $streamRepository = $entityManager->getRepository(Stream::class);
            $startTime = time();
            $timeout = 30;

            while (time() - $startTime < $timeout) {
                $stream = $streamRepository->getStream();

                if (!is_null($stream)) {
                    $chunk = $stream->getChunk();
                    $streamRepository->removeStream($stream);
                    if ($chunk === 'finished_stream') break;

                    echo "data: " . json_encode($chunk) . "\n\n";
                    flush();
                } else {
                    echo "data: " . json_encode('') . "\n\n";
                    flush();
                }
            }

            echo "event: done\n";
            echo "data: done\n\n";
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive'
        ]);
    }

    #[Route('/api/assistant/prompt/file', name:'assistant_prompt_file', methods:['POST'])]
    public function addFile(Request $request): Response
    {
        $file = $request->files->get('file');
        $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
        try {
            $file->move($uploadDirectory, $file->getClientOriginalName());
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
        return new JsonResponse(['status' => 'ok'], 200);
    }

    #[Route('/api/assistant/template', 'template_save', methods:['POST'])]
    public function saveTemplate(Request $request): Response
    {
        $templateDto = new TemplateLmmDto(
            $request->get('name'),
            $request->get('content')
        );

        $this->validatorService->validateAndThrow($templateDto);

        $this->templateRepository->saveTemplate($templateDto);

       return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/api/assistant/save/chat-settings', 'assistent_save_settings', methods:['POST'])]
    public function saveChatSettings(Request $request): Response
    {
        $requestData = json_decode($request->getContent());

        if ($requestData === null) {
            return new JsonResponse(['error' => 'Invalid JSON.'], Response::HTTP_BAD_REQUEST);
        }

        $settings = new SettingLmmDto(
            $requestData->name ?? null,
            $requestData->model ?? null,
            $requestData->temperature ?? null,
            $requestData->maxToken ?? null,
        );
        $this->validatorService->validateAndThrow($settings);
        $this->settingsLmmRepository->saveSettings($settings);

        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/api/assistant/save/chat-settings/default', 'assistent_save_setting_default', methods:['POST'])]
    public function saveTemplateAsDefault(Request $request, SettingsLmmRepository $settingsLmmRepository): Response
    {
        $requestData = json_decode($request->getContent());
        if ($requestData === null) {
            return new JsonResponse(['error' => 'Invalid JSON.'], Response::HTTP_BAD_REQUEST);
        }

        $this->settingsLmmRepository->setDefaultSetting($requestData->id);

        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/api/assistant/test', 'test-stream')]
    public function streamAction(): StreamedResponse
    {
        $data = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Doe'],
            ['id' => 3, 'name' => 'Mary Smith'],
            ['id' => 4, 'name' => 'Michael Brown'],
        ];

        return new StreamedResponse(function () use ($data) {
            foreach ($data as $item) {
                echo "data: " . json_encode($item) . "\n\n";

                flush();

                sleep(1);
            }

            echo "event: done\n";
            echo "data: Stream finished\n\n";

            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Dla serwera proxy jak Nginx (przyspiesza wysyłanie w trybie SSE)
        ]);
    }

}
