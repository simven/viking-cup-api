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

    public function findByFirstNameLastName(?string $firstName, ?string $lastName): ?Person
    {
        $normalizedFirstName = $this->normalize($firstName);
        $normalizedLastName = $this->normalize($lastName);

        $qb = $this->createQueryBuilder('p')
            ->where('LOWER(p.firstName) LIKE :firstName')
            ->andWhere('LOWER(p.lastName) LIKE :lastName')
            ->setParameter('firstName', "%$normalizedFirstName%")
            ->setParameter('lastName', "%$normalizedLastName%");

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByEmail(string $email): ?Person
    {
        $normalizedEmail = $this->normalize($email);

        $qb = $this->createQueryBuilder('p')
            ->where('LOWER(p.email) LIKE :email')
            ->setParameter('email', "%$normalizedEmail%");

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findPersonsPaginated(
        ?string $sort = null,
        ?string $order = null,
        ?string $person = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p');

        if ($person !== null) {
            $normalizedPerson = $this->normalize($person);
            $qb->where('LOWER(p.firstName) LIKE :person')
                ->orWhere('LOWER(p.lastName) LIKE :person')
                ->orWhere('LOWER(p.email) LIKE :person')
                ->orWhere('LOWER(p.phone) LIKE :person')
                ->setParameter('person', '%' . $normalizedPerson . '%');
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
        }

        return $qb;
    }

    public function findMediasPaginated(
        ?string $sort = null,
        ?string $order = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?bool   $selected = null,
        ?bool   $selectedMailSent = null,
        ?bool   $eLearningMailSent = null,
        ?bool   $briefingSeen = null,
        ?bool   $generatePass = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.medias', 'm');

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
        if ($eLearningMailSent !== null) {
            $qb->andWhere('m.eLearningMailSent = :eLearningMailSent')
                ->setParameter('eLearningMailSent', $eLearningMailSent);
        }
        if ($briefingSeen !== null) {
            $qb->andWhere('m.briefingSeen = :briefingSeen')
                ->setParameter('briefingSeen', $briefingSeen);
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
        }

        return $qb;
    }

    public function findPilotsPaginated(
        ?string $sort = null,
        ?string $order = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?int    $eventId = null,
        ?int    $roundId = null,
        ?int    $categoryId = null,
        ?string $number = null,
        ?bool   $ffsaLicensee = null,
        ?string $ffsaNumber = null,
        ?string $nationality = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.pilot', 'pi')
            ->leftJoin('pi.pilotEvents', 'pe', 'WITH', 'pe.event = :event')
            ->setParameter('event', $eventId);

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
        if ($eventId !== null && $number !== null) {
            $qb->andWhere('pe.pilotNumber = :number')
                ->setParameter('number', $number);
        }
        if ($roundId !== null || $categoryId !== null) {
            $qb->innerJoin('pi.pilotRoundCategories', 'prc');

            if ($roundId !== null) {
                $qb->andWhere('prc.round = :roundId')
                    ->setParameter('roundId', $roundId);
            }
            if ($categoryId !== null) {
                $qb->andWhere('prc.category = :categoryId')
                    ->setParameter('categoryId', $categoryId);
            }
        }
        if ($ffsaLicensee !== null) {
            $qb->andWhere('pi.ffsaLicensee = :ffsaLicensee')
                ->setParameter('ffsaLicensee', $ffsaLicensee);
        }
        if ($ffsaNumber !== null) {
            $qb->andWhere('pi.ffsaNumber LIKE :ffsaNumber')
                ->setParameter('ffsaNumber', '%' . $ffsaNumber . '%');
        }
        if ($nationality !== null) {
            $qb->andWhere('LOWER(p.nationality) LIKE :nationality')
                ->setParameter('nationality', '%' . $this->normalizeName($nationality) . '%');
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
            case 'number':
                $qb->orderBy('pe.pilotNumber', $order);
                break;
            case 'ffsaLicensee':
                $qb->orderBy('pi.ffsaLicensee', $order);
                break;
            case 'ffsaNumber':
                $qb->orderBy('pi.ffsaNumber', $order);
                break;
            case 'nationality':
                $qb->orderBy('p.nationality', $order);
                break;

        }

        return $qb;
    }

    public function findMembersPaginated(
        ?string $sort = null,
        ?string $order = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $roleAsso = null,
        ?string $roleVcup = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.member', 'm');

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
        if ($roleAsso !== null) {
            $qb->andWhere('m.roleAsso LIKE :roleAsso')
                ->setParameter('roleAsso', '%' . $roleAsso . '%');
        }
        if ($roleVcup !== null) {
            $qb->andWhere('m.roleVcup LIKE :roleVcup')
                ->setParameter('roleVcup', '%' . $roleVcup . '%');
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
            case 'roleAsso':
                $qb->orderBy('m.roleAsso', $order);
                break;
            case 'roleVcup':
                $qb->orderBy('m.roleVcup', $order);
                break;
        }

        return $qb;
    }

    public function findCommissairesPaginated(
        ?string $sort = null,
        ?string $order = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $licenceNumber = null,
        ?string $asaCode = null,
        ?int    $typeId = null,
        ?bool   $isFlag = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.commissaires', 'c');

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
        if ($licenceNumber !== null) {
            $qb->andWhere('c.licenceNumber LIKE :licenceNumber')
                ->setParameter('licenceNumber', '%' . $licenceNumber . '%');
        }
        if ($asaCode !== null) {
            $qb->andWhere('c.asaCode LIKE :asaCode')
                ->setParameter('asaCode', '%' . $asaCode . '%');
        }
        if ($typeId !== null) {
            $qb->andWhere('c.type = :typeId')
                ->setParameter('typeId', $typeId);
        }
        if ($isFlag !== null) {
            $qb->andWhere('c.isFlag = :isFlag')
                ->setParameter('isFlag', $isFlag);
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
            case 'licenceNumber':
                $qb->orderBy('c.licenceNumber', $order);
                break;
            case 'asaCode':
                $qb->orderBy('c.asaCode', $order);
                break;
            case 'type':
                $qb->orderBy('ct.type', $order);
                break;
            case 'isFlag':
                $qb->orderBy('c.isFlag', $order);
                break;
        }

        return $qb;
    }

    public function findVolunteersPaginated(
        ?string $sort = null,
        ?string $order = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?int    $roleId = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.volunteers', 'v');

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
        if ($roleId !== null) {
            $qb->andWhere('v.role = :roleId')
                ->setParameter('roleId', $roleId);
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
            case 'role':
                $qb->orderBy('v.role', $order);
                break;
        }

        return $qb;
    }

    public function findRescuersPaginated(
        ?string $sort = null,
        ?string $order = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $role = null
    ): QueryBuilder
    {
        $order = $order ?? 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.rescuers', 'r');

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
        if ($role !== null) {
            $qb->andWhere('r.role LIKE :role')
                ->setParameter('role', '%' . $role . '%');
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
            case 'role':
                $qb->orderBy('r.role', $order);
                break;
        }

        return $qb;
    }

    private function normalize(string $str): string
    {
        $normalized = strtolower(trim($str));
        return preg_replace('/\s+/', ' ', $normalized);
    }
}
