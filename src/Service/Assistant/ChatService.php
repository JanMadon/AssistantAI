<?php

namespace App\Service\Assistant;

use App\DTO\LMM\Prompt\PromptDto;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\LMM\ChatClientServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class ChatService
{
    public function __construct(
        private readonly EntityManagerInterface     $entityManager,
        private readonly ConversationRepository     $conversationRepository,
        private readonly MessageRepository          $messageRepository,
        private readonly ChatClientServiceInterface $chatClinetService,

    ) {}

    public function chat(PromptDto $promptDto): PromptDto
    {

        if($promptDto->conversation_id === null){
            $conversation = $this->conversationRepository->CreateAndSaveNewConversation($promptDto);
        }else {
            $conversation = $this->conversationRepository->getConversationById($promptDto->conversation_id);
        }

        $this->messageRepository->createNewMessage($promptDto, $conversation);
        $this->entityManager->flush();
        $chatRes = $this->chatClinetService->prompt($conversation);
        $this->messageRepository->createNewMessage($chatRes, $conversation);

        return $chatRes;
    }

}