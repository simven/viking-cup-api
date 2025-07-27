<?php

namespace App\Business;

use App\Dto\VolunteerDto;
use App\Entity\Volunteer;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Entity\Round;
use App\Helper\LinkHelper;
use App\Repository\PersonRepository;
use App\Repository\PersonTypeRepository;
use App\Repository\RoundDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Serializer\SerializerInterface;

readonly class VolunteerBusiness
{
    public function __construct(
        private PersonTypeRepository   $personTypeRepository,
        private PersonRepository       $personRepository,
        private RoundDetailRepository  $roundDetailRepository,
        private LinkHelper             $linkHelper,
        private SerializerInterface    $serializer,
        private EntityManagerInterface $em
    )
    {}

    public function getVolunteers(
        int $page,
        int $limit,
        ?string $sort = null,
        ?string $order = null,
        ?int    $eventId = null,
        ?int    $roundId = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $role = null
    ): array
    {
        $persons = $this->personRepository->findVolunteersPaginated($sort, $order, $name, $email, $phone, $role);

        $adapter = new QueryAdapter($persons, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $persons = $pager->getCurrentPageResults();

        $volunteerPersons = [];
        /** @var Person $person */
        foreach ($persons as $person) {
            $personArray = $this->serializer->normalize($person, 'json', ['groups' => ['person', 'personPersonType', 'personType', 'personRoundDetails', 'roundDetail', 'personLinks', 'link', 'linkLinkType', 'linkType']]);

            $volunteers = $person->getVolunteers()->filter(function (Volunteer $volunteer) use ($eventId, $roundId, $role) {
                return (!$eventId || $volunteer->getRound()->getEvent()->getId() === $eventId) &&
                    (!$roundId || $volunteer->getRound()->getId() === $roundId) &&
                    (!$role || str_contains($volunteer->getRole(), $role) !== false);
            });

            $personArray['volunteers'] = array_values($volunteers->toArray());

            if (!empty($personArray['volunteers'])) {
                $volunteerPersons[] = $personArray;
            }
        }

        return [
            'pagination' => [
                'totalItems' => $totalItems,
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ],
            'volunteers' => $volunteerPersons
        ];
    }

    public function createPersonVolunteer(Round $round, VolunteerDto $volunteerDto): void
    {
        $personType = $this->personTypeRepository->find(4);
        $person = $this->createPerson($volunteerDto, $personType, $round);

        if (!empty($volunteerDto->instagram)) {
            $this->linkHelper->upsertInstagramLink($person, $volunteerDto->instagram);
        }

        $this->createVolunteer($person, $round, $volunteerDto);

        $this->em->flush();
    }

    public function createPerson(VolunteerDto $volunteerDto, PersonType $personType, Round $round): Person
    {
        $person = $this->personRepository->findOneBy(['email' => $volunteerDto->email, 'personType' => $personType]);
        if ($person === null) {
            $person = new Person();
            $person->setEmail($volunteerDto->email)
                ->setPersonType($personType);
        }

        $person->setFirstName($volunteerDto->firstName)
            ->setLastName($volunteerDto->lastName)
            ->setPhone($volunteerDto->phone)
            ->addRound($round);

        $this->updatePersonPresence($person, $volunteerDto->presence);

        $this->em->persist($person);

        return $person;
    }

    public function createVolunteer(Person $person, Round $round, VolunteerDto $volunteerDto): Volunteer
    {
        // get round volunteer or create a new one
        $volunteer = $person->getVolunteers()->filter(fn($volunteer) => $volunteer->getRound()?->getId() === $round->getId())->first();
        if ($volunteer === false) {
            $volunteer = new Volunteer();
            $volunteer->setPerson($person)
                ->setRound($round);
        }
        $volunteer->setRole($volunteerDto->role);

        $this->em->persist($volunteer);

        return $volunteer;
    }

    public function updatePersonVolunteer(Volunteer $volunteer, VolunteerDto $volunteerDto): void
    {
        // update person
        $person = $volunteer->getPerson();

        $person->setFirstName($volunteerDto->firstName)
            ->setLastName($volunteerDto->lastName)
            ->setEmail($volunteerDto->email)
            ->setPhone($volunteerDto->phone)
            ->setWarnings($volunteerDto->warnings);

        $this->updatePersonPresence($person, $volunteerDto->presence);

        $this->em->persist($person);

        // update instagram link
        if (!empty($volunteerDto->instagram)) {
            $this->linkHelper->upsertInstagramLink($person, $volunteerDto->instagram);
        }

        // update volunteer
        $volunteer->setRole($volunteerDto->role);

        $this->em->persist($volunteer);

        $this->em->flush();
    }

    private function updatePersonPresence(Person $person, array $presence): void
    {
        // Supprimer les détails de rounds qui ne sont plus dans la liste de présence
        foreach ($person->getRoundDetails()->toArray() as $roundDetail) {
            if (!in_array($roundDetail->getId(), $presence)) {
                $person->removeRoundDetail($roundDetail);
            }
        }

        // Ajouter les nouveaux détails de rounds
        foreach ($presence as $roundDetailId) {
            // Vérifier si le détail de round existe déjà
            if ($person->getRoundDetails()->exists(fn($key, $rd) => $rd->getId() === $roundDetailId)) {
                continue;
            }

            $roundDetail = $this->roundDetailRepository->find($roundDetailId);
            if ($roundDetail !== null) {
                $person->addRoundDetail($roundDetail);
            }
        }
    }

    public function deleteVolunteer(Volunteer $volunteer): void
    {
        $this->em->remove($volunteer);
        $this->em->flush();
    }
}