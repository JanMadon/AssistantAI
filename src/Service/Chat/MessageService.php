<?php

namespace App\Service\Chat;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class MessageService
{
    private EntityManagerInterface $entityManager;
    private MessageRepository $messageRepository;

    public function __construct(EntityManagerInterface $entityManager, MessageRepository $messageRepository)
    {
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
    }

    public function saveMessage($data,Conversation $conversation): void
    {
        $message = new Message();
        $message->setConversation($conversation);
        if(is_string($data)){
            $message->setAuthor('AI');
            $message->setContent($data);
        } else {
            $message->setAuthor('User');
            $message->setContent(end($data->conversation)->User);
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();

    }

}