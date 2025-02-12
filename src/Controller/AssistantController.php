<?php
namespace App\Controller;

use App\DTO\LMM\SettingLmmDto;
use App\DTO\LMM\TemplateLmmDto;
use App\Repository\ConversationRepository;
use App\Repository\LMM\SettingsLmmRepository;
use App\Repository\TemplateRepository;
use App\Service\Chat\ConversationService;
use App\Service\Chat\MessageService;
use App\Service\GPTservice;
use App\Service\Validators\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;


class AssistantController extends AbstractController
{
    public function __construct(
        private readonly GPTservice $gptService,
        private readonly ConversationRepository $conversationRepository,
        private readonly TemplateRepository $templateRepository,
        private readonly ValidatorService $validatorService
    ){}

    #[Route('/assistant', 'assistant', methods: ['GET'])]
    public function assistant(Request $request, SerializerInterface $serializer): Response
    {

        $conversations = $this->conversationRepository->findBy([], ['id' => 'DESC']);
        $conversationsJson = $serializer->serialize($conversations, 'json', ['groups' => 'conversation']);
        $templates = $this->templateRepository->findBy([], ['id' => 'DESC']);;
        return $this->render('assistant/main.html.twig', [
            'conversations' => $conversations,
            'conversationsJson' => $conversationsJson,
            'templates' => $templates,
            'models' => $this->gptService->getChatModels(),
        ]);
    }

    #[Route('/api/assistant/prompt', 'assistent_prompt', methods:['POST'])]
    public function assistantConversation(
        Request $request,
        ConversationService $conversationService,
        MessageService $messageService): Response
    {
        // request {id,system, conversation, model}
        $requestData = json_decode($request->getContent());
        $conversation = $conversationService->getOrCreateConversation($requestData);

        $messageService->saveMessage($requestData, $conversation);

        $answer = $this->gptService->prompt(
            $requestData->system,
            $requestData->conversation,
            $requestData->model,
            $requestData->config);

        $messageService->saveMessage($answer, $conversation);

        return new JsonResponse([
            'answer' => $answer,
            'id' => $conversation->getId(),
        ], 200);
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
    public function saveChatSettings(Request $request, SettingsLmmRepository $settingsLmmRepository): Response
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

        $settingsLmmRepository->saveSettings($settings);

        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/api/assistant/test', 'assistent_testt')]
    public function onlyAssistantConversation(Request $request, EntityManagerInterface $entityManager): Response
    {
        // request {id,system, conversation}
        $request = json_decode($request->getContent(), true);
        $messageUser = end($request['conversation']);
        $conversationId = $request['id'] ?? null;



        $answer = $this->gptService->prompt('gpt-3.5-turbo', $request['system'], $request['conversation']);
        print_r($answer);

        return new JsonResponse([
            'answer' => $answer,
            'id' => $conversationId,
        ], 200);
    }
}
