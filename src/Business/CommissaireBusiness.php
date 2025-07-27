<?php

namespace App\Business;

use App\Dto\CommissaireDto;
use App\Entity\Commissaire;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Entity\Round;
use App\Repository\PersonRepository;
use App\Repository\PersonTypeRepository;
use App\Repository\RoundDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Serializer\SerializerInterface;

readonly class CommissaireBusiness
{
    public function __construct(
        private PersonTypeRepository   $personTypeRepository,
        private PersonRepository       $personRepository,
        private RoundDetailRepository  $roundDetailRepository,
        private SerializerInterface    $serializer,
        private EntityManagerInterface $em
    )
    {}

    public function getCommissaires(
        int $page,
        int $limit,
        ?string $sort = null,
        ?string $order = null,
        ?int    $eventId = null,
        ?int    $roundId = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $licenceNumber = null,
        ?string $asaCode = null,
        ?string $type = null,
        ?bool   $isFlag = null
    ): array
    {
        $persons = $this->personRepository->findCommissairesPaginated($sort, $order, $name, $email, $phone, $licenceNumber, $asaCode, $type, $isFlag);

        $adapter = new QueryAdapter($persons, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $persons = $pager->getCurrentPageResults();

        $commissairePersons = [];
        /** @var Person $person */
        foreach ($persons as $person) {
            $personArray = $this->serializer->normalize($person, 'json', ['groups' => ['person', 'personPersonType', 'personType', 'personRoundDetails', 'roundDetail']]);

            $commissaires = $person->getCommissaires()->filter(function (Commissaire $commissaire) use ($eventId, $roundId, $licenceNumber, $asaCode, $type, $isFlag) {
                return (!$eventId || $commissaire->getRound()->getEvent()->getId() === $eventId) &&
                    (!$roundId || $commissaire->getRound()->getId() === $roundId) &&
                    (!$licenceNumber || str_contains($commissaire->getLicenceNumber(), $licenceNumber) !== false) &&
                    (!$asaCode || str_contains($commissaire->getAsaCode(), $asaCode) !== false) &&
                    (!$type || str_contains($commissaire->getType(), $type) !== false) &&
                    ($isFlag === null || $commissaire->isFlag() === $isFlag);
            });

            $personArray['commissaires'] = array_values($commissaires->toArray());

            if (!empty($personArray['commissaires'])) {
                $commissairePersons[] = $personArray;
            }
        }

        return [
            'pagination' => [
                'totalItems' => $totalItems,
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ],
            'commissaires' => $commissairePersons
        ];
    }

    public function createPersonCommissaire(Round $round, CommissaireDto $commissaireDto): void
    {
        $personType = $this->personTypeRepository->find(3);
        $person = $this->createPerson($commissaireDto, $personType, $round);

        $this->createCommissaire($person, $round, $commissaireDto);

        $this->em->flush();
    }

    public function createPerson(CommissaireDto $commissaireDto, PersonType $personType, Round $round): Person
    {
        $person = $this->personRepository->findOneBy(['email' => $commissaireDto->email, 'personType' => $personType]);
        if ($person === null) {
            $person = new Person();
            $person->setEmail($commissaireDto->email)
                ->setPersonType($personType);
        }

        $person->setFirstName($commissaireDto->firstName)
            ->setLastName($commissaireDto->lastName)
            ->setPhone($commissaireDto->phone)
            ->addRound($round);

        $this->updatePersonPresence($person, $commissaireDto->presence);

        $this->em->persist($person);

        return $person;
    }

    public function createCommissaire(Person $person, Round $round, CommissaireDto $commissaireDto): Commissaire
    {
        // get round commissaire or create a new one
        $commissaire = $person->getCommissaires()->filter(fn($commissaire) => $commissaire->getRound()?->getId() === $round->getId())->first();
        if ($commissaire === false) {
            $commissaire = new Commissaire();
            $commissaire->setPerson($person)
                ->setRound($round);
        }
        $commissaire->setType($commissaireDto->commissaireType)
            ->setLicenceNumber($commissaireDto->licenceNumber)
            ->setAsaCode($commissaireDto->asaCode)
            ->setIsFlag($commissaireDto->isFlag);

        $this->em->persist($commissaire);

        return $commissaire;
    }

    public function updatePersonCommissaire(Commissaire $commissaire, CommissaireDto $commissaireDto): void
    {
        // update person
        $person = $commissaire->getPerson();

        $person->setFirstName($commissaireDto->firstName)
            ->setLastName($commissaireDto->lastName)
            ->setEmail($commissaireDto->email)
            ->setPhone($commissaireDto->phone)
            ->setWarnings($commissaireDto->warnings);

        $this->updatePersonPresence($person, $commissaireDto->presence);

        $this->em->persist($person);

        // update commissaire
        $commissaire->setType($commissaireDto->commissaireType)
            ->setLicenceNumber($commissaireDto->licenceNumber)
            ->setAsaCode($commissaireDto->asaCode)
            ->setIsFlag($commissaireDto->isFlag);

        $this->em->persist($commissaire);

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

    public function deleteCommissaire(Commissaire $commissaire): void
    {
        $this->em->remove($commissaire);
        $this->em->flush();
    }
}