<?php

namespace App\Service\Assistant;

use App\DTO\LMM\Prompt\PromptDto;
use App\DTO\LMM\Prompt\ResponseLmmDto;
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
        private readonly ChatClientServiceInterface $chatClientService,

    ) {}

    public function chat(PromptDto $promptDto): ResponseLmmDto
    {

        if($promptDto->conversation_id === null){
            $conversation = $this->conversationRepository->CreateAndSaveNewConversation($promptDto);
        }else {
            $conversation = $this->conversationRepository->getConversationById($promptDto->conversation_id);
        }

        $this->messageRepository->createNewMessage($conversation, $promptDto->role, $promptDto->content);
        $this->entityManager->flush();
        $gptRes = $this->chatClientService->prompt($conversation);
        $this->messageRepository->createNewMessage($conversation, $gptRes->role, $gptRes->content);

        return $gptRes;
    }

}