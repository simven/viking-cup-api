<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PilotRoundCategory>
 */
class PilotRoundCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PilotRoundCategory::class);
    }

    public function findWithCorrectPilotEvent(PilotRoundCategory $pilotRoundCategory): ?PilotRoundCategory
    {
        $qb = $this->createQueryBuilder('prc')
            ->select('prc, p, pe')
            ->innerJoin('prc.pilot', 'p')
            ->leftJoin('p.pilotEvents', 'pe', 'WITH', 'pe.event = :event')
            ->andWhere('prc = :pilotRoundCategory')
            ->setParameter('pilotRoundCategory', $pilotRoundCategory)
            ->setParameter('event', $pilotRoundCategory->getRound()->getEvent());

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByRoundCategoryQuery(
        Round $round,
        Category $category,
        ?string $sort,
        ?string $order,
        ?string $search = null
    ): Query
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('prc')
            ->select('prc, p, pe')
            ->innerJoin('prc.pilot', 'p')
            ->leftJoin('p.pilotEvents', 'pe', 'WITH', 'pe.event = :event')
            ->andWhere('prc.round = :round')
            ->andWhere('prc.category = :category')
            ->setParameter('round', $round)
            ->setParameter('category', $category)
            ->setParameter('event', $round->getEvent());

        if ($search !== null) {
            $qb->andWhere('pe.event = :event')
                ->andWhere('
                    p.firstName LIKE :pilot OR
                    p.lastName LIKE :pilot OR
                    CONCAT(p.firstName, \' \', p.lastName) LIKE :pilot OR
                    CONCAT(p.lastName, \' \', p.firstName) LIKE :pilot OR
                    p.email LIKE :pilot OR
                    pe.pilotNumber LIKE :pilot
                ')
                ->setParameter('event', $round->getEvent())
                ->setParameter('pilot', "%$search%");
        }

        switch ($sort) {
            case 'pilotName':
                $qb->addOrderBy('p.lastName', $order);
                break;
            case 'isCompeting':
                $qb->addOrderBy('prc.isCompeting', $order);
                break;
            case 'pilotNumber':
                $qb->addOrderBy('pe.pilotNumber', $order);
                break;
            default:
                $qb->addOrderBy('pe.pilotNumber', $order);
        }

        return $qb->getQuery();
    }

    //    /**
    //     * @return PilotRoundCategory[] Returns an array of PilotRoundCategory objects
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

    //    public function findOneBySomeField($value): ?PilotRoundCategory
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
