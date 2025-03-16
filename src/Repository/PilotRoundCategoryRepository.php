<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findByRoundCategory(Round $round, Category $category, ?string $pilot = null): array
    {
        $qb = $this->createQueryBuilder('prc')
            ->andWhere('prc.round = :round')
            ->andWhere('prc.category = :category')
            ->setParameter('round', $round)
            ->setParameter('category', $category);

        if ($pilot !== null) {
            $qb->innerJoin('prc.pilot', 'p')
                ->andWhere('
                    p.firstName LIKE :pilot OR
                    p.lastName LIKE :pilot OR
                    CONCAT(p.firstName, \' \', p.lastName) LIKE :pilot OR
                    CONCAT(p.lastName, \' \', p.firstName) LIKE :pilot OR
                    p.email LIKE :pilot OR
                    p.phoneNumber LIKE :pilot
                ')
                ->setParameter('pilot', "%$pilot%");
        }

        return $qb->getQuery()
            ->getResult();
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
