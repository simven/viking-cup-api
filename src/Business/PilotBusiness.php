<?php

namespace App\Business;

use App\Dto\PilotDto;
use App\Entity\Pilot;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Repository\PersonRepository;
use App\Repository\PersonTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

readonly class PilotBusiness
{
    public function __construct(
        private PersonTypeRepository   $personTypeRepository,
        private PersonRepository       $personRepository,
        private EntityManagerInterface $em
    )
    {}

    public function getPilots(
        int $page,
        int $limit,
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
        ?string $ffsaNumber = null
    ): array
    {
        $pilotPersonsQuery = $this->personRepository->findPilotsPaginated($sort, $order, $name, $email, $phone, $eventId, $roundId, $categoryId, $number, $ffsaLicensee, $ffsaNumber);

        $adapter = new QueryAdapter($pilotPersonsQuery, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $pilotPersons = $pager->getCurrentPageResults();

        return [
            'pilots' => $pilotPersons,
            'pagination' => [
                'totalItems' => $totalItems,
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ]
        ];
    }

    public function createPersonPilot(PilotDto $pilotDto): void
    {
        $personType = $this->personTypeRepository->find(6);
        $person = $this->createPerson($pilotDto, $personType);

        $this->createPilot($person, $pilotDto);

        $this->em->flush();
    }

    private function createPerson(PilotDto $pilotDto, PersonType $personType): Person
    {
        $person = $this->personRepository->findOneBy(['email' => $pilotDto->email, 'personType' => $personType]);
        if ($person === null) {
            $person = new Person();
            $person->setEmail($pilotDto->email)
                ->setPersonType($personType);
        }

        $person->setFirstName($pilotDto->firstName)
            ->setLastName($pilotDto->lastName)
            ->setPhone($pilotDto->phone)
            ->setComment($pilotDto->comment)
            ->setWarnings($pilotDto->warnings);

        $this->em->persist($person);

        return $person;
    }

    private function createPilot(Person $person, PilotDto $pilotDto): Pilot
    {
        // get pilot or create new one
        $pilot = $person->getPilot();
        if ($pilot === null) {
            $pilot = new Pilot();
            $pilot->setPerson($person);
        }

        $pilot->setFfsaLicensee($pilotDto->ffsaLicensee)
            ->setFfsaNumber($pilotDto->ffsaNumber);

        $this->em->persist($pilot);

        return $pilot;
    }

    public function updatePersonPilot(Pilot $pilot, PilotDto $pilotDto): void
    {
        // update person
        $person = $pilot->getPerson();

        $person->setFirstName($pilotDto->firstName)
            ->setLastName($pilotDto->lastName)
            ->setEmail($pilotDto->email)
            ->setPhone($pilotDto->phone);

        $this->em->persist($person);

        // update pilot
        $pilot->setFfsaLicensee($pilotDto->ffsaLicensee)
            ->setFfsaNumber($pilotDto->ffsaNumber);

        $this->em->persist($pilot);

        $this->em->flush();
    }

    public function deletePilot(Pilot $pilot): void
    {
        $this->em->remove($pilot);
        $this->em->flush();
    }
}