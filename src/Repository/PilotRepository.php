<?php

namespace App\Repository;

use App\Entity\Pilot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pilot>
 */
class PilotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pilot::class);
    }

    public function findByFirstNameLastName(?string $firstName, ?string $lastName): ?Pilot
    {
        $normalizedFirstName = $this->normalizeName($firstName);
        $normalizedLastName = $this->normalizeName($lastName);

        $qb = $this->createQueryBuilder('p')
            ->where('LOWER(p.firstName) LIKE :firstName')
            ->andWhere('LOWER(p.lastName) LIKE :lastName')
            ->setParameter('firstName', "%$normalizedFirstName%")
            ->setParameter('lastName', "%$normalizedLastName%");

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByName(?string $pilotName): ?Pilot
    {
        $normalized = $this->normalizeName($pilotName);
        $nameParts = explode(' ', $normalized);

        if (empty($nameParts)) {
            return null;
        }

        $qb = $this->createQueryBuilder('p');

        if (count($nameParts) === 1) {
            $qb->where('LOWER(p.firstName) LIKE :name')
                ->orWhere('LOWER(p.lastName) LIKE :name')
                ->setParameter('name', "%$nameParts[0]%");
        } else {
            $conditions = [];

            foreach ($nameParts as $index => $part) {
                $paramKey = "name$index";
                $conditions[] = "(LOWER(p.firstName) LIKE :$paramKey OR LOWER(p.lastName) LIKE :$paramKey)";
                $qb->setParameter($paramKey, "%$part%");
            }

            $qb->where(implode(' AND ', $conditions));
        }

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    private function normalizeName(string $name): string
    {
        $normalized = strtolower(trim($name));
        return preg_replace('/\s+/', ' ', $normalized);
    }

//    /**
//     * @return Pilot[] Returns an array of Pilot objects
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

//    public function findOneBySomeField($value): ?Pilot
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
