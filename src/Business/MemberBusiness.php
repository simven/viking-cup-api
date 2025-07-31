<?php

namespace App\Business;

use App\Dto\CreateMemberDto;
use App\Dto\MemberDto;
use App\Entity\Member;
use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

readonly class MemberBusiness
{
    public function __construct(
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

    public function createMember(CreateMemberDto $memberDto): Member
    {
        $person = $this->personRepository->find($memberDto->personId);
        if ($memberDto->personId === null || $person === null) {
            throw new Exception('Person not found');
        }

        // get member or create new one
        $member = $person->getMember();
        if ($member === null) {
            $member = new Member();
            $member->setPerson($person);
        }

        $member->setRoleAsso($memberDto->roleAsso)
            ->setRoleVcup($memberDto->roleVcup);

        $this->em->persist($member);
        $this->em->flush();

        return $member;
    }

    public function updatePersonMember(Member $member, MemberDto $memberDto): void
    {
        // update person
        $person = $member->getPerson();

        $person->setFirstName($memberDto->firstName)
            ->setLastName($memberDto->lastName)
            ->setEmail($memberDto->email)
            ->setPhone($memberDto->phone)
            ->setAddress($memberDto->address)
            ->setCity($memberDto->city)
            ->setZipCode($memberDto->zipCode)
            ->setCountry($memberDto->country);

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