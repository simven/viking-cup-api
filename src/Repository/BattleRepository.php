<?php

namespace App\Repository;

use App\Entity\Battle;
use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Battle>
 */
class BattleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Battle::class);
    }

    /**
     * @param Round $round
     * @param Category $category
     * @param int|null $passage
     * @return Battle[]
     */
    public function getBattleVersus(Round $round, Category $category, int $passage = null): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.pilotRoundCategory1', 'prc1')
            ->leftJoin('b.pilotRoundCategory2', 'prc2')
            ->andWhere('prc1 IS NULL OR prc1.round = :round')
            ->andWhere('prc1 IS NULL OR prc1.category = :category')
            ->andWhere('prc2 IS NULL OR prc2.round = :round')
            ->andWhere('prc2 IS NULL OR prc2.category = :category')
            ->setParameter('round', $round)
            ->setParameter('category', $category);

        if ($passage !== null) {
            $qb->andWhere('b.passage = :passage')
                ->setParameter('passage', $passage);
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function getBattleRanking(Round $round, Category $category)
    {
        return $this->createQueryBuilder('b')
            ->select([
                'prc',
                'SUM(CASE WHEN b.winner = prc THEN 1 ELSE 0 END) AS wins',
                'SUM(CASE WHEN b.winner != prc THEN 1 ELSE 0 END) AS loses',
                'MAX(CASE WHEN b.winner != prc THEN b.passage ELSE 0 END) AS last_defeat_passage'
            ])
            ->leftJoin('b.pilotRoundCategory1', 'prc1')
            ->leftJoin('b.pilotRoundCategory2', 'prc2')
            ->innerJoin(PilotRoundCategory::class, 'prc', Join::WITH, 'b.pilotRoundCategory1 = prc OR b.pilotRoundCategory2 = prc')
            ->andWhere('prc1 IS NULL OR prc1.round = :round')
            ->andWhere('prc1 IS NULL OR prc1.category = :category')
            ->andWhere('prc2 IS NULL OR prc2.round = :round')
            ->andWhere('prc2 IS NULL OR prc2.category = :category')
            ->setParameter('round', $round)
            ->setParameter('category', $category)
            ->groupBy('prc')
            ->orderBy('wins', 'DESC')
            ->addOrderBy('last_defeat_passage', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Battle[] Returns an array of Battle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Battle
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
