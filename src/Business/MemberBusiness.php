<?php

namespace App\Business;

use App\Dto\MemberDto;
use App\Entity\Member;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Repository\PersonRepository;
use App\Repository\PersonTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

readonly class MemberBusiness
{
    public function __construct(
        private PersonTypeRepository   $personTypeRepository,
        private PersonRepository       $personRepository,
        private EntityManagerInterface $em
    )
    {}

    public function getMembers(
        int $page,
        int $limit,
        ?string $sort = null,
        ?string $order = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $roleAsso = null,
        ?string $roleVcup = null
    ): array
    {
        $memberPersonsQuery = $this->personRepository->findMembersPaginated($sort, $order, $name, $email, $phone, $roleAsso, $roleVcup);

        $adapter = new QueryAdapter($memberPersonsQuery, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $memberPersons = $pager->getCurrentPageResults();

        return [
            'members' => $memberPersons,
            'pagination' => [
                'totalItems' => $totalItems,
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ]
        ];
    }

    public function createPersonMember(MemberDto $memberDto): void
    {
        $personType = $this->personTypeRepository->find(2);
        $person = $this->createPerson($memberDto, $personType);

        $this->createMember($person, $memberDto);

        $this->em->flush();
    }

    private function createPerson(MemberDto $memberDto, PersonType $personType): Person
    {
        $person = $this->personRepository->findOneBy(['email' => $memberDto->email, 'personType' => $personType]);
        if ($person === null) {
            $person = new Person();
            $person->setEmail($memberDto->email)
                ->setPersonType($personType);
        }

        $person->setFirstName($memberDto->firstName)
            ->setLastName($memberDto->lastName)
            ->setPhone($memberDto->phone);

        $this->em->persist($person);

        return $person;
    }

    private function createMember(Person $person, MemberDto $memberDto): Member
    {
        // get member or create new one
        $member = $person->getMember();
        if ($member === null) {
            $member = new Member();
            $member->setPerson($person);
        }

        $member->setRoleAsso($memberDto->roleAsso)
            ->setRoleVcup($memberDto->roleVcup);

        $this->em->persist($member);

        return $member;
    }

    public function updatePersonMember(Member $member, MemberDto $memberDto): void
    {
        // update person
        $person = $member->getPerson();

        $person->setFirstName($memberDto->firstName)
            ->setLastName($memberDto->lastName)
            ->setEmail($memberDto->email)
            ->setPhone($memberDto->phone);

        $this->em->persist($person);

        // update member
        $member->setRoleAsso($memberDto->roleAsso)
            ->setRoleVcup($memberDto->roleVcup);

        $this->em->persist($member);

        $this->em->flush();
    }

    public function deleteMember(Member $member): void
    {
        $this->em->remove($member);
        $this->em->flush();
    }
}