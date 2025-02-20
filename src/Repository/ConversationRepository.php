<?php

namespace App\Repository;

use App\DTO\LMM\Prompt\PromptDto;
use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Conversation::class);
    }

    //    /**
    //     * @return Conversation[] Returns an array of Conversation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Conversation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findOrCreate(?int $id){
        if($id){
            $conversation = $this->find($id);
        } else {
            $conversation = new Conversation;
        }
        return $conversation;
    }

    public function getLastEntryId(): ?int
    {
        $entity = $this->findOneBy([], ['id' => 'DESC']);
        return $entity ? $entity->getId() : 0;
    }

    public function CreateNewConversation(PromptDto $prompt): Conversation
    {
        $conversation = new Conversation();
        $conversation->setName($prompt->content);
        $conversation->setSystemField($prompt->system_field);
        $conversation->setModelId($prompt->model);
        $conversation->setTemperature($prompt->temperature);
        $conversation->setMaxToken($prompt->max_token);
        $this->entityManager->persist($conversation);

        return $conversation;
    }

    public function CreateAndSaveNewConversation(PromptDto $prompt): Conversation
    {
        $conversation = $this->CreateNewConversation($prompt);
        $this->entityManager->flush();
        return $conversation;
    }

    public function getConversationById($id): Conversation
    {
        return $this->find($id);
    }
}
