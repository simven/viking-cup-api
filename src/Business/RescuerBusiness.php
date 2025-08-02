<?php

namespace App\Business;

use App\Dto\CreateRescuerDto;
use App\Dto\RescuerDto;
use App\Entity\Rescuer;
use App\Entity\Person;
use App\Repository\PersonRepository;
use App\Repository\RoundDetailRepository;
use App\Repository\RoundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

readonly class RescuerBusiness
{
    public function __construct(
        private PersonRepository       $personRepository,
        private RoundRepository        $roundRepository,
        private RoundDetailRepository  $roundDetailRepository,
        private SerializerInterface    $serializer,
        private EntityManagerInterface $em
    )
    {}

    public function getRescuers(
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
        $personIdsTotal = $this->personRepository->findFilteredRescuerPersonIdsPaginated($sort, $order, $name, $email, $phone, $role);
        $persons = $this->personRepository->findPersonsByIds($personIdsTotal['items']);

        $rescuerPersons = [];
        /** @var Person $person */
        foreach ($persons as $person) {
            $personArray = $this->serializer->normalize($person, 'json', ['groups' => ['person', 'personRoundDetails', 'roundDetail', 'personLinks', 'link', 'linkLinkType', 'linkType']]);

            $rescuers = $person->getRescuers()->filter(function (Rescuer $rescuer) use ($eventId, $roundId, $role) {
                return (!$eventId || $rescuer->getRound()->getEvent()->getId() === $eventId) &&
                    (!$roundId || $rescuer->getRound()->getId() === $roundId) &&
                    (!$role || str_contains($rescuer->getRole(), $role) !== false);
            });

            $personArray['rescuers'] = array_values($rescuers->toArray());

            if (!empty($personArray['rescuers'])) {
                $rescuerPersons[] = $personArray;
            }
        }

        return [
            'pagination' => [
                'totalItems' => $personIdsTotal['total'],
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ],
            'rescuers' => $rescuerPersons
        ];
    }

    public function createRescuer(CreateRescuerDto $rescuerDto): ?Rescuer
    {
        $person = $this->personRepository->find($rescuerDto->personId);
        if ($rescuerDto->personId === null || $person === null) {
            throw new Exception('Person not found');
        }

        $round = $this->roundRepository->find($rescuerDto->roundId);
        if ($rescuerDto->roundId === null || $round === null) {
            throw new Exception('Round not found');
        }

        // get round rescuer or create a new one
        $rescuer = $person->getRescuers()->filter(fn(Rescuer $rescuer) => $rescuer->getRound()?->getId() === $round->getId())->first();
        if ($rescuer === false) {
            $rescuer = new Rescuer();
            $rescuer->setPerson($person)
                ->setRound($round);
        }
        $rescuer->setRole($rescuerDto->role);

        $this->em->persist($rescuer);
        $this->em->flush();

        return $rescuer;
    }

    public function updatePersonRescuer(Rescuer $rescuer, RescuerDto $rescuerDto): void
    {
        // update person
        $person = $rescuer->getPerson();

        $person->setFirstName($rescuerDto->firstName)
            ->setLastName($rescuerDto->lastName)
            ->setEmail($rescuerDto->email)
            ->setPhone($rescuerDto->phone)
            ->setWarnings($rescuerDto->warnings);

        $this->updatePersonPresence($person, $rescuerDto->presence);

        $this->em->persist($person);

        // update rescuer
        $rescuer->setRole($rescuerDto->role);

        $this->em->persist($rescuer);

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

    public function deleteRescuer(Rescuer $rescuer): void
    {
        $this->em->remove($rescuer);
        $this->em->flush();
    }
}