<?php
namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\SettingsLmm;
use App\Entity\Template;
use App\Repository\ConversationRepository;
use App\Repository\TemplateRepository;
use App\Service\Chat\ConversationService;
use App\Service\Chat\MessageService;
use App\Service\GPTservice;
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
        private readonly EntityManagerInterface $entityManager
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
        $template = new Template();
        $template->setName($request->get('name'));
        $template->setContent($request->get('content'));
        $this->entityManager->persist($template);
        $this->entityManager->flush();

       return new JsonResponse('ok');
    }

    #[Route('/api/assistant/save/chat-settings', 'assistent_save_settings', methods:['POST'])]
    public function saveChatSettings(Request $request): Response
    {
        $request = json_decode($request->getContent());

        $settings = new SettingsLmm();
        $settings->setName($request->name);
        $settings->setModelId($request->model);
        $settings->setTemperature($request->temperature);
        $settings->setMaxToken($request->maxToken);
        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        return new JsonResponse('ok');
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
