<?php

namespace App\Service\Chat;

use AllowDynamicProperties;
use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConversationService
{


    private EntityManagerInterface $entityManager;
    private ConversationRepository $conversationRepository;

    public function __construct(EntityManagerInterface $entityManager, ConversationRepository $conversationRepository)
    {
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
    }

    public function getOrCreateConversation($requestData)
    {
        $lastMessage = end($requestData->conversation);
        $conversationId = $requestData->id ?? null;

        if (!$conversationId) {
            $conversation = new Conversation();
            $conversation->setName($lastMessage->User);
        } else {
            $conversation = $this->conversationRepository->find($conversationId);
        }
        $conversation->setSystemField($requestData->system);
        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $conversation;
    }

}