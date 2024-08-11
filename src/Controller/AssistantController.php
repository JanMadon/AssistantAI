<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Service\GPTservice;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use phpDocumentor\Reflection\Type;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;


class AssistantController extends AbstractController
{

    private GPTservice $gptService;
    private ConversationRepository $conversationRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        GPTservice $gptService,
        ConversationRepository $conversationRepository,
        EntityManagerInterface $entityManager)
    {
        $this->gptService = $gptService;
        $this->conversationRepository = $conversationRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/assistant', 'assistant', methods: ['GET'])]
    public function assistant(Request $request, SerializerInterface $serializer): Response
    {
        $conversations = $this->conversationRepository->findAll();
        $conversationsJson = $serializer->serialize($conversations, 'json', ['groups' => 'conversation']);
        return $this->render('assistant/main.html.twig', [
            'conversations' => $conversations,
            'conversationsJson' => $conversationsJson,
        ]);
    }

    #[Route('/api/assistant/prompt', 'assistent_prompt', methods:['POST'])]
    public function assistantConversation(Request $request): Response
    {
        // request {id,system, conversation}
        $request = json_decode($request->getContent(), true);
        $messageUser = end($request['conversation']);
        $conversationId = $request['id'] ?? null;

        $ConvRepository = $this->entityManager->getRepository(Conversation::class);

        if (!$conversationId) {
            $conversation = new Conversation();
            $conversation->setName(reset($messageUser));
        } else {
            $conversation =  $ConvRepository->find($conversationId);
        }
        $conversation->setSystemField($request['system']);
        $this->entityManager->persist($conversation);
        $this->entityManager->flush($conversation);
        $conversationId = $conversation->getId();

        $message = new Message();
        $message->setAuthor(array_key_first($messageUser));
        $message->setContent(reset($messageUser));
        $message->setConversation($conversation);
        $this->entityManager->persist($message);
        $this->entityManager->flush($message);

        $answer = $this->gptService->prompt('gpt-3.5-turbo', $request['system'], $request['conversation']);

        $messageAi = new Message();
        $messageAi->setAuthor('AI');
        $messageAi->setContent($answer);
        $messageAi->setConversation($conversation);
        $this->entityManager->persist($messageAi);
        $this->entityManager->flush($messageAi);

        return new JsonResponse([
            'answer' => $answer,
            'id' => $conversationId,
        ], 200);
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
