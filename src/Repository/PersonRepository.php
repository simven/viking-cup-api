<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function findAllPaginated(
        ?string $sort = null,
        ?string $order = null,
        ?string $personType = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.personType', 'pt');

        if ($personType !== null) {
            $qb->andWhere('pt.name = :personType')
                ->setParameter('personType', $personType);
        }

        switch ($sort) {
            case 'firstName':
                $qb->orderBy('p.firstName', $order);
                break;
            case 'lastName':
                $qb->orderBy('p.lastName', $order);
                break;
            case 'phone':
                $qb->orderBy('p.phone', $order);
                break;
            case 'email':
                $qb->orderBy('p.email', $order);
                break;
            case 'personeType':
                $qb->orderBy('pt.name', $order);
                break;
        }

        return $qb;
    }

//    /**
//     * @return Person[] Returns an array of Person objects
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

//    public function findOneBySomeField($value): ?Person
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
