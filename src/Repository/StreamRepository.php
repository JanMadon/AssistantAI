<?php

namespace App\Repository;

use App\Entity\Stream;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stream>
 */
class StreamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stream::class);
    }

    //    /**
    //     * @return Stream[] Returns an array of Stream objects
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

    //    public function findOneBySomeField($value): ?Stream
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getStream(): ?Stream
    {
        return $this->findOneBy([], ['id' => 'ASC']);
    }

    public function removeStream(Stream $chunk):void
    {
        $this->getEntityManager()->remove($chunk);
        $this->getEntityManager()->flush();
    }

    public function save(string $chunk): void
    {
        $entity = new Stream();
        $entity->setChunk($chunk);
        $entity->setRead(false);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
