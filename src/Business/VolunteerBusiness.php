<?php

namespace App\Business;

use App\Dto\CreateVolunteerDto;
use App\Dto\VolunteerDto;
use App\Entity\Volunteer;
use App\Entity\Person;
use App\Entity\Round;
use App\Helper\LinkHelper;
use App\Repository\PersonRepository;
use App\Repository\RoundDetailRepository;
use App\Repository\RoundRepository;
use App\Repository\VolunteerRoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Serializer\SerializerInterface;

readonly class VolunteerBusiness
{
    public function __construct(
        private PersonRepository       $personRepository,
        private RoundRepository        $roundRepository,
        private RoundDetailRepository  $roundDetailRepository,
        private VolunteerRoleRepository $volunteerRoleRepository,
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
        ?int    $roleId = null
    ): array
    {
        $persons = $this->personRepository->findVolunteersPaginated($sort, $order, $name, $email, $phone, $roleId);

        $adapter = new QueryAdapter($persons, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $persons = $pager->getCurrentPageResults();

        $volunteerPersons = [];
        /** @var Person $person */
        foreach ($persons as $person) {
            $personArray = $this->serializer->normalize($person, 'json', ['groups' => ['person', 'personRoundDetails', 'roundDetail', 'personLinks', 'link', 'linkLinkType', 'linkType']]);

            $volunteers = $person->getVolunteers()->filter(function (Volunteer $volunteer) use ($eventId, $roundId, $roleId) {
                return (!$eventId || $volunteer->getRound()->getEvent()->getId() === $eventId) &&
                    (!$roundId || $volunteer->getRound()->getId() === $roundId) &&
                    (!$roleId || $volunteer->getRole()?->getId() === $roleId);
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

    public function createVolunteer(CreateVolunteerDto $volunteerDto): Volunteer
    {
        $person = $this->personRepository->find($volunteerDto->personId);
        if ($volunteerDto->personId === null || $person === null) {
            throw new Exception('Person not found');
        }

        $round = $this->roundRepository->find($volunteerDto->roundId);
        if ($volunteerDto->roundId === null || $round === null) {
            throw new Exception('Round not found');
        }

        if (!empty($volunteerDto->roleId)) {
            $role = $this->volunteerRoleRepository->find($volunteerDto->roleId);
        }

        // get round volunteer or create a new one
        $volunteer = $person->getVolunteers()->filter(fn(Volunteer $volunteer) => $volunteer->getRound()?->getId() === $round->getId())->first();
        if ($volunteer === false) {
            $volunteer = new Volunteer();
            $volunteer->setPerson($person)
                ->setRound($round);
        }
        $volunteer->setRole($role ?? null);

        $this->em->persist($volunteer);
        $this->em->flush();

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
        if (!empty($volunteerDto->roleId)) {
            $role = $this->volunteerRoleRepository->find($volunteerDto->roleId);
        }
        $volunteer->setRole($role ?? null);

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