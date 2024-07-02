<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Service\GPTservice;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class AssistantController extends AbstractController
{

    private GPTservice $gptService;
    private ConversationRepository $conversationRepository;

    public function __construct(GPTservice $gptService, ConversationRepository $conversationRepository)
    {
        $this->gptService = $gptService;
        $this->conversationRepository = $conversationRepository;
    }

    #[Route('/assistant', 'assistant')]
    public function assistant(Request $request): Response
    {
        // $number = random_int(0, 100);
        // dd($this->getParameter('API_KEY_AIDEVS')); 

        // TODO: tezba bedzie pobrać z db ostatnią rozmwę;
       
        //dd($this->conversationRepository->getLastEntryId());

        return $this->render('assistant.html.twig');
    }

    #[Route('/api/assistant/prompt', 'assistent_prompt')]
    public function assistantConversation(Request $request, EntityManagerInterface $entityManager): Response
    {
        // request {id,system, conversation}
        $request = json_decode($request->getContent(), true);
        $mesageUser = end($request['conversation']);
        $conversationId = $request['id'] ?? null; 

        $ConvRepository = $entityManager->getRepository(Conversation::class);
    
        if (!$conversationId) {
            $conversation = new Conversation();
        } else {
            $conversation =  $ConvRepository->find($conversationId);
        }
        $conversation->setDescription($request['system']);
        $entityManager->persist($conversation);
        $entityManager->flush($conversation);
        $conversationId = $conversation->getId();
    
        $mesage = new Message();
        $mesage->setAuthor(array_key_first($mesageUser));
        $mesage->setContent(reset($mesageUser));
        $mesage->setConversation($conversation);
        $entityManager->persist($mesage);
        $entityManager->flush($mesage);
    
        $answer = $this->gptService->prompt('gpt-3.5-turbo', $request['system'], $request['conversation']);

        $messageAi = new Message();
        $messageAi->setAuthor('AI');
        $messageAi->setContent($answer);
        $messageAi->setConversation($conversation);
        $entityManager->persist($messageAi);
        $entityManager->flush($messageAi);

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
        $mesageUser = end($request['conversation']);
        $conversationId = $request['id'] ?? null; 


    
        $answer = $this->gptService->prompt('gpt-3.5-turbo', $request['system'], $request['conversation']);
        print_r($answer);

        return new JsonResponse([
            'answer' => $answer,
            'id' => $conversationId,
        ], 200);
    }
}
