<?php

namespace App\Repository\LMM;

use App\Entity\SettingsLmm;
use App\ValueObjects\LLM\SettingsLmmVO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class SettingsLmmRepository extends ServiceEntityRepository
{

    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, SettingsLmm::class);
    }

    public function saveSettings(SettingsLmmVO $settingsLmmVO): void
    {
        $settings = new SettingsLmm();
        $settings->setName($settingsLmmVO->getName());
        $settings->setModelId($settingsLmmVO->getModel());
        $settings->setTemperature($settingsLmmVO->getTemperature());
        $settings->setMaxToken($settingsLmmVO->getMaxToken());
        $this->entityManager->persist($settings);
        $this->entityManager->flush();
    }



    //    /**
    //     * @return SettingsLmm[] Returns an array of SettingsLmm objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?SettingsLmm
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
