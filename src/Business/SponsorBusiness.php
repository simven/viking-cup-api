<?php

namespace App\Business;

use App\Dto\CreateSponsorDto;
use App\Dto\SponsorDto;
use App\Dto\SponsorshipCounterpartDto;
use App\Dto\SponsorshipDto;
use App\Entity\Person;
use App\Entity\Sponsor;
use App\Entity\Sponsorship;
use App\Entity\SponsorshipCounterpart;
use App\Helper\FileHelper;
use App\Repository\RoundRepository;
use App\Repository\SponsorRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

readonly class SponsorBusiness
{
    public function __construct(
        private SponsorRepository      $sponsorRepository,
        private RoundRepository        $roundRepository,
        private FileHelper             $fileHelper,
        private SerializerInterface    $serializer,
        private EntityManagerInterface $em
    )
    {}

    public function getSponsors(
        int $page,
        int $limit,
        ?string $sort = null,
        ?string $order = null,
        ?string $name = null,
        ?string $contact = null,
        ?int    $eventId = null,
        ?int    $roundId = null,
        ?string $status = null,
        ?string $counterpartType = null,
        ?int    $minAmount = null,
        ?int    $maxAmount = null,
        ?string $otherCounterpart = null,
        ?bool   $hasContract = null
    ): array
    {
        $sponsorIdsTotal = $this->sponsorRepository->findFilteredSponsorIdsPaginated($page, $limit, $sort, $order, $name, $contact, $eventId, $roundId, $status, $counterpartType, $minAmount, $maxAmount, $otherCounterpart, $hasContract);
        $sponsors = $this->sponsorRepository->findSponsorsByIds($sponsorIdsTotal['items']);

        $sponsorPersons = [];
        /** @var Sponsor $sponsor */
        foreach ($sponsors as $sponsor) {
            $sponsorArray = $this->serializer->normalize($sponsor, null, ['groups' => ['sponsor', 'sponsorLinks', 'sponsorPerson', 'person', 'personRoundDetails', 'roundDetail', 'personLinks', 'link', 'linkLinkType', 'linkType']]);

            $sponsorships = $sponsor->getSponsorships()->filter(function (Sponsorship $sponsorship) use ($eventId, $roundId, $status, $counterpartType, $minAmount, $maxAmount, $otherCounterpart, $hasContract) {
                return (!$eventId || $sponsorship->getEvent()->getId() === $eventId) &&
                    (!$roundId || $sponsorship->getRound()->getId() === $roundId) &&
                    (!$status || strtolower($sponsorship->getStatus()->value) === strtolower($status)) &&
                    (!$counterpartType || $sponsorship->getSponsorshipCounterparts()->exists(function ($key, $counterpart) use ($counterpartType) {
                        return $counterpart->getCounterpartType()?->value === $counterpartType;
                    })) &&
                    (!$minAmount || $sponsorship->getSponsorshipCounterparts()->exists(function ($key, $counterpart) use ($minAmount) {
                        return $counterpart->getAmount() !== null && $counterpart->getAmount() >= $minAmount;
                    })) &&
                    (!$maxAmount || $sponsorship->getSponsorshipCounterparts()->exists(function ($key, $counterpart) use ($maxAmount) {
                        return $counterpart->getAmount() !== null && $counterpart->getAmount() <= $maxAmount;
                    })) &&
                    (!$otherCounterpart || $sponsorship->getSponsorshipCounterparts()->exists(function ($key, $counterpart) use ($otherCounterpart) {
                        return str_contains(strtolower($counterpart->getOtherCounterpart() ?? ''), strtolower($otherCounterpart));
                    })) &&
                    ($hasContract === null || ($hasContract && $sponsorship->getContractFilePath() !== null) || (!$hasContract && $sponsorship->getContractFilePath() === null));
            });

            $sponsorArray['sponsorships'] = array_values($sponsorships->toArray());

            $sponsorPersons[] = $sponsorArray;
        }

        return [
            'pagination' => [
                'totalItems' => $sponsorIdsTotal['total'],
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ],
            'sponsors' => $sponsorPersons
        ];
    }

    public function createSponsor(CreateSponsorDto $sponsorDto): ?Sponsor
    {
        $person = $this->personRepository->find($sponsorDto->personId);
        if ($sponsorDto->personId === null || $person === null) {
            throw new Exception('Person not found');
        }

        $round = $this->roundRepository->find($sponsorDto->roundId);
        if ($sponsorDto->roundId === null || $round === null) {
            throw new Exception('Round not found');
        }

        // get round sponsor or create a new one
        $sponsor = $person->getSponsors()->filter(fn(Sponsor $sponsor) => $sponsor->getRound()?->getId() === $round->getId())->first();
        if ($sponsor === false) {
            $sponsor = new Sponsor();
            $sponsor->setPerson($person)
                ->setRound($round);
        }
        $sponsor->setRole($sponsorDto->role);

        $this->em->persist($sponsor);
        $this->em->flush();

        return $sponsor;
    }

    public function updatePersonSponsor(Sponsor $sponsor, SponsorDto $sponsorDto): void
    {
        // update sponsor
        $sponsor->setName($sponsorDto->name)
            ->setDescription($sponsorDto->description)
            ->setDisplayWebsite($sponsorDto->displayWebsite)
            ->setAlt($sponsorDto->alt);

        // update contact
        $contact = $sponsor->getContact();

        if ($contact === null) {
            $contact = new Person();
            $sponsor->setContact($contact);
        }
        $contact->setFirstName($sponsorDto->firstName)
            ->setLastName($sponsorDto->lastName)
            ->setEmail($sponsorDto->email)
            ->setPhone($sponsorDto->phone)
            ->setWarnings($sponsorDto->warnings)
            ->setComment($sponsorDto->comment);

        $this->em->persist($sponsor);
        $this->em->persist($contact);

        // update sponsorships
        $sponsorshipsDto = $this->serializer->denormalize($sponsorDto->sponsorships, SponsorshipDto::class . '[]');
        $this->updateSponsorships($sponsor, $sponsorshipsDto);

        $this->em->flush();
    }

    public function deleteSponsor(Sponsor $sponsor): void
    {
        $this->em->remove($sponsor);
        $this->em->flush();
    }

    public function deleteSponsorshipContract(Sponsorship $sponsorship): void
    {
        $this->fileHelper->deleteFile($sponsorship->getContractFilePath());

        $sponsorship->setContractFilePath(null);
        $this->em->persist($sponsorship);
        $this->em->flush();
    }

    private function updateSponsorships(Sponsor $sponsor, array $sponsorshipsDto): void
    {
        $sponsorships = $sponsor->getSponsorships();

        // delete sponsorships that are not in the DTO
        $this->deleteSponsorships($sponsorships, $sponsorshipsDto);

        /** @var SponsorshipDto $sponsorshipDto */
        foreach ($sponsorshipsDto as $sponsorshipDto) {
            if ($sponsorshipDto->id) {
                $sponsorship = $sponsorships->filter(fn(Sponsorship $s) => $s->getId() === $sponsorshipDto->id)->first();
                if ($sponsorship === false) {
                    continue;
                }
            } else {
                $sponsorship = new Sponsorship();
                $sponsorship->setSponsor($sponsor);
            }

            if ($sponsorshipDto->eventId) {
                $event = $this->roundRepository->find($sponsorshipDto->eventId);
            }
            if ($sponsorshipDto->roundId) {
                $round = $this->roundRepository->find($sponsorshipDto->roundId);
            }

            $sponsorship->setEvent($event ?? null)
                ->setRound($round ?? null)
                ->setStatus($sponsorshipDto->status);

            $counterpartsDto = $this->serializer->denormalize($sponsorshipDto->counterparts, SponsorshipCounterpartDto::class . '[]');
            $this->updateCounterparts($sponsorship, $counterpartsDto);

            $this->em->persist($sponsorship);
        }
    }

    /**
     * Deletes sponsorships from the database.
     *
     * @param Sponsorship[] $sponsorships
     */
    private function deleteSponsorships(Collection $sponsorships, array $sponsorshipsDto): void
    {
        $sponsorshipDtoIds = array_map(fn(SponsorshipDto $dto) => $dto->id, $sponsorshipsDto);
        $sponsorshipsToDelete = $sponsorships->filter(fn(Sponsorship $s) => !in_array($s->getId(), $sponsorshipDtoIds));

        foreach ($sponsorshipsToDelete as $sponsorship) {
            $this->em->remove($sponsorship);
        }
    }

    private function updateCounterparts(Sponsorship $sponsorship, array $counterpartsDto): void
    {
        $counterparts = $sponsorship->getSponsorshipCounterparts();

        // delete counterparts that are not in the DTO
        $this->deleteCounterparts($counterparts, $counterpartsDto);

        // update or create counterparts
        foreach ($counterpartsDto as $counterpartDto) {
            if ($counterpartDto->id) {
                $counterpart = $counterparts->filter(fn(SponsorshipCounterpart $c) => $c->getId() === $counterpartDto->id)->first();
                if ($counterpart === false) {
                    continue;
                }
            } else {
                $counterpart = new SponsorshipCounterpart();
                $counterpart->setSponsorship($sponsorship);
                $sponsorship->addSponsorCounterpart($counterpart);
            }

            $counterpart->setCounterpartType($counterpartDto->counterpartType)
                ->setAmount($counterpartDto->amount)
                ->setOtherCounterpart($counterpartDto->otherCounterpart);

            $this->em->persist($counterpart);
        }
    }

    private function deleteCounterparts(Collection $counterparts, array $counterpartsDto): void
    {
        $counterpartDtoIds = array_map(fn(SponsorshipCounterpartDto $dto) => $dto->id, $counterpartsDto);
        $counterpartsToDelete = $counterparts->filter(fn(SponsorshipCounterpart $c) => !in_array($c->getId(), $counterpartDtoIds));

        foreach ($counterpartsToDelete as $counterpart) {
            $this->em->remove($counterpart);
        }
    }
}