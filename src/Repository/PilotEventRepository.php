<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\PilotEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PilotEvent>
 */
class PilotEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PilotEvent::class);
    }

    public function getLastPilotNumberForCategory(Event $event, Category $category): int
    {
        $qb = $this->createQueryBuilder('pe')
            ->select('MAX(pe.pilotNumber) as maxPilotNumber')
            ->innerJoin('pe.pilot', 'p')
            ->innerJoin('p.pilotRoundCategories', 'prd')
            ->where('pe.event = :event')
            ->andWhere('prd.category = :category')
            ->setParameter('event', $event)
            ->setParameter('category', $category);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    //    /**
    //     * @return PilotEvent[] Returns an array of PilotEvent objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PilotEvent
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
