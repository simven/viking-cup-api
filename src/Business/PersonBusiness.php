<?php

namespace App\Business;

use App\Dto\PersonDto;
use App\Entity\Person;
use App\Helper\LinkHelper;
use App\Repository\PersonRepository;
use App\Repository\RoundDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

readonly class PersonBusiness
{
    public function __construct(
        private PersonRepository       $personRepository,
        private RoundDetailRepository  $roundDetailRepository,
        private LinkHelper             $linkHelper,
        private EntityManagerInterface $em
    )
    {}

    public function getPersons(
        int $page,
        int $limit,
        ?string $sort = null,
        ?string $order = null,
        ?string $person = null
    ): array
    {
        $persons = $this->personRepository->findPersonsPaginated($sort, $order, $person);

        $adapter = new QueryAdapter($persons, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $persons = $pager->getCurrentPageResults();

        return [
            'pagination' => [
                'totalItems' => $totalItems,
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ],
            'persons' => $persons
        ];
    }

    public function createPerson(PersonDto $personDto): Person
    {
        $person = $this->personRepository->findOneBy(['email' => $personDto->email]);
        if ($person === null) {
            $person = new Person();
            $person->setEmail($personDto->email);
        }

        $person->setFirstName($personDto->firstName)
            ->setLastName($personDto->lastName)
            ->setPhone($personDto->phone)
            ->setAddress($personDto->address)
            ->setCity($personDto->city)
            ->setPostalCode($personDto->zipCode)
            ->setCountry($personDto->country)
            ->setWarnings($personDto->warnings)
            ->setComment($personDto->comment);

        $this->updatePersonPresence($person, $personDto->presence);

        if (!empty($personDto->instagram)) {
            $this->linkHelper->upsertInstagramLink($person, $personDto->instagram);
        }

        $this->em->persist($person);
        $this->em->flush();

        return $person;
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

                if (!$person->getRounds()->contains($roundDetail->getRound())) {
                    $person->addRound($roundDetail->getRound());
                }
            }
        }
    }
}