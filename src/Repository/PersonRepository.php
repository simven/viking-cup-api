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
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?bool $selected = null,
        ?bool $selectedMailSent = null,
        ?bool $watchBriefing = null,
        ?bool $generatePass = null,
        ?string $personType = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.personType', 'pt')
            ->innerJoin('p.medias', 'm');

        if ($personType !== null) {
            $qb->andWhere('pt.name = :personType')
                ->setParameter('personType', $personType);
        }
        if ($name !== null) {
            $qb->andWhere('p.firstName LIKE :name OR p.lastName LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }
        if ($email !== null) {
            $qb->andWhere('p.email LIKE :email')
                ->setParameter('email', '%' . $email . '%');
        }
        if ($phone !== null) {
            $qb->andWhere('p.phone LIKE :phone')
                ->setParameter('phone', '%' . $phone . '%');
        }
        if ($selected !== null) {
            $qb->andWhere('m.selected = :selected')
                ->setParameter('selected', $selected);
        }
        if ($selectedMailSent !== null) {
            $qb->andWhere('m.selectedMailSent = :selectedMailSent')
                ->setParameter('selectedMailSent', $selectedMailSent);
        }
        if ($watchBriefing !== null) {
            $qb->andWhere('m.watchBriefing = :watchBriefing')
                ->setParameter('watchBriefing', $watchBriefing);
        }
        if ($generatePass !== null) {
            $qb->andWhere('m.generatePass = :generatePass')
                ->setParameter('generatePass', $generatePass);
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
