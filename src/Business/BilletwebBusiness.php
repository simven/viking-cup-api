<?php

namespace App\Business;

use App\Dto\BilletwebTicketDto;
use App\Entity\BilletwebTicket;
use App\Entity\Category;
use App\Entity\Pilot;
use App\Entity\PilotEvent;
use App\Entity\PilotRoundCategory;
use App\Entity\Qualifying;
use App\Entity\Round;
use App\Helper\ConfigHelper;
use App\Repository\BilletwebTicketRepository;
use App\Repository\CategoryRepository;
use App\Repository\PilotEventRepository;
use App\Repository\PilotRepository;
use App\Repository\PilotRoundCategoryRepository;
use App\Repository\QualifyingRepository;
use App\Repository\RoundRepository;
use App\Service\BilletwebService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class BilletwebBusiness
{
    public function __construct(
        private readonly BilletwebTicketRepository    $billetwebRepository,
        private readonly RoundRepository              $roundRepository,
        private readonly CategoryRepository           $categoryRepository,
        private readonly PilotRepository              $pilotRepository,
        private readonly PilotRoundCategoryRepository $pilotRoundCategoryRepository,
        private readonly PilotEventRepository         $pilotEventRepository,
        private readonly QualifyingRepository         $qualifyingRepository,
        private readonly BilletwebService             $billetwebService,
        private readonly ConfigHelper                 $configHelper,
        private readonly EntityManagerInterface       $em,
        private SerializerInterface                   $serializer
    )
    {
        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $arrayDenormalizer = new ArrayDenormalizer();
        $this->serializer = new Serializer([$normalizer, $arrayDenormalizer]);
    }

    public function syncEventAttendees(): void
    {
        $pilotEventIds = $this->configHelper->getValue('PILOT_EVENT_IDS');
        $pilotEventIds = explode(',', $pilotEventIds);

        foreach ($pilotEventIds as $pilotEventId) {
            $eventPilotsData = $this->billetwebService->getEventAttendees($pilotEventId);

            $eventTickets = $this->serializer->denormalize($eventPilotsData, BilletwebTicketDto::class . '[]', 'json', [
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true
            ]);

            $doubleMountingTickets = [];
            foreach ($eventTickets as $eventTicket) {
                try {
                    $billetwebTicket = $this->upsertBilletwebFromDto($eventTicket);

                    if ($billetwebTicket->getPass() === -1) {
                        continue;
                    }

                    // Get Round Entity
                    $round = $this->roundRepository->findOneBy(['name' => $billetwebTicket->getTicketLabel()]);

                    // Get Category Entity
                    $category = $this->categoryRepository->findOneBy(['name' => $billetwebTicket->getCategory()]);

                    // Create Pilot Entity
                    $pilot = $this->pilotRepository->findByFirstNameLastName($billetwebTicket->getPilotFirstName(), $billetwebTicket->getPilotLastName());
                    if ($pilot === null) {
                        $pilot = new Pilot();
                        $pilot->setEmail($billetwebTicket->getPilotEmail())
                            ->setFirstName($billetwebTicket->getPilotFirstName())
                            ->setLastName($billetwebTicket->getPilotLastName())
                            ->setPhoneNumber($billetwebTicket->getCustom()['Portable'] ?? null)
                            ->setAddress($billetwebTicket->getCustom()['Adresse'] ?? null)
                            ->setZipCode($billetwebTicket->getCustom()['Code postal'] ?? null)
                            ->setCity($billetwebTicket->getCustom()['Ville'] ?? null)
                            ->setCountry($billetwebTicket->getCustom()['Pays'] ?? null)
                            ->setNationality($billetwebTicket->getCustom()['Nationalité'] ?? null)
                            ->setFfsaLicensee(boolval($billetwebTicket->getCustom()['Etes-vous licencié FFSA ?'] ?? null));
                        if ($pilot->getCreatedAt() === null && $billetwebTicket->getCreationDate() !== null) {
                            $pilot->setCreatedAt($billetwebTicket->getCreationDate());
                        }

                        $this->em->persist($pilot);
                    }

                    $pilotEvent = $this->pilotEventRepository->findOneBy(['pilot' => $pilot, 'event' => $round->getEvent()]);
                    if ($pilotEvent === null) {
                        $pilotEvent = new PilotEvent();
                        $pilotEvent->setPilot($pilot)
                            ->setEvent($round->getEvent())
                            ->setReceiveWindscreenBand(false);

                        $pilotNumberCounter = $category->getPilotNumberCounter();
                        $pilotNumber = $pilotNumberCounter->getPilotNumberCounter() + 1;
                        $pilotNumberCounter->setPilotNumberCounter($pilotNumber);
                        $this->em->persist($pilotNumberCounter);

                        $pilotEvent->setPilotNumber($pilotNumber);

                        $this->em->persist($pilotEvent);
                    }

                    // Create PilotRoundCategory Entity
                    $doubleMounting = boolval($billetwebTicket->getCustom()['Double monte '] ?? null);
                    $vehicle = $billetwebTicket->getCustom()['Véhicule pour participer à la compétition'] ?? null;

                    if ($doubleMounting === true) {
                        $doubleMountingTickets[] = [
                            'ticket' => $billetwebTicket,
                            'pilot' => $pilot,
                            'round' => $round,
                            'category' => $category,
                            'vehicle' => $vehicle
                        ];
                    } else {
                        $this->createPilotRoundCategory($pilot, $round, $category, $vehicle);
                    }

                    $this->em->flush();
                } catch (\Throwable $e) {}
            }


            $this->createDoubleMountPilotRoundCategory($doubleMountingTickets);
            $this->em->flush();
        }
    }

    private function upsertBilletwebFromDto(BilletwebTicketDto $billetwebDto): ?BilletwebTicket
    {
        $billetweb = $this->billetwebRepository->find($billetwebDto->id);

        if ($billetweb !== null) {
            return $billetweb;
        }

        $billetweb = new BilletwebTicket();
        $billetweb->setId($billetwebDto->id)
            ->setTicketNumber($billetwebDto->extId)
            ->setBarcode($billetwebDto->barcode)
            ->setCreationDate(new \DateTime($billetwebDto->orderDate))
            ->setTicketLabel($billetwebDto->ticket)
            ->setCategory($billetwebDto->category)
            ->setPilotLastName($billetwebDto->name)
            ->setPilotFirstName($billetwebDto->firstname)
            ->setPilotEmail($billetwebDto->email)
            ->setBuyerLastName($billetwebDto->orderName)
            ->setBuyerFirstName($billetwebDto->orderFirstname)
            ->setBuyerEmail($billetwebDto->orderEmail)
            ->setOrderNumber($billetwebDto->orderExtId)
            ->setPaymentType($billetwebDto->orderPaymentType)
            ->setAmount($billetwebDto->price)
            ->setPaid($billetwebDto->orderPaid)
            ->setUsed($billetwebDto->used)
            ->setUsedDate(!empty($billetwebDto->usedDate) && $billetwebDto->usedDate !== '0000-00-00 00:00:00' ? new \DateTime($billetwebDto->usedDate) : null)
            ->setPass((int)$billetwebDto->pass)
            ->setCustom($billetwebDto->custom)
            ->setPack((int)$billetwebDto->pass === -1);

        $this->em->persist($billetweb);

        return $billetweb;
    }

    private function createDoubleMountPilotRoundCategory(array $doubleMountingTickets): void
    {
        $pilotAssociation = [];
        foreach ($doubleMountingTickets as $doubleMountingTicket) {
            $mainPilotName = $doubleMountingTicket['ticket']->getCustom()['Nom du pilote principal'] ?? null;

            $mainPilot = $this->pilotRepository->findByName($mainPilotName);
            if ($mainPilot === null) {
                continue;
            }

            if ($mainPilot->getId() !== $doubleMountingTicket['pilot']->getId()) {
                $this->createPilotRoundCategory(
                    $doubleMountingTicket['pilot'],
                    $doubleMountingTicket['round'],
                    $doubleMountingTicket['category'],
                    $doubleMountingTicket['vehicle'],
                    false
                );

                $pilotAssociation[$mainPilot->getId()] = $doubleMountingTicket['pilot'];
            }
        }

        foreach ($doubleMountingTickets as $doubleMountingTicket) {
            $mainPilotName = $doubleMountingTicket['ticket']->getCustom()['Nom du pilote principal'] ?? null;

            $mainPilot = $this->pilotRepository->findByName($mainPilotName);
            if ($mainPilot === null) {
                continue;
            }

            if ($mainPilot->getId() === $doubleMountingTicket['pilot']->getId() && isset($pilotAssociation[$doubleMountingTicket['pilot']->getId()])) {
                $this->createPilotRoundCategory(
                    $doubleMountingTicket['pilot'],
                    $doubleMountingTicket['round'],
                    $doubleMountingTicket['category'],
                    $doubleMountingTicket['vehicle'],
                    true,
                    $pilotAssociation[$doubleMountingTicket['pilot']->getId()]
                );
            }
        }

    }

    private function createPilotRoundCategory(Pilot $pilot, Round $round, Category $category, ?string $vehicle, bool $isMainPilot = true, ?Pilot $secondPilot = null): void
    {
        $pilotRoundCategory = $this->pilotRoundCategoryRepository->findOneBy(['pilot' => $pilot, 'round' => $round, 'category' => $category]);
        if ($pilotRoundCategory === null) {
            $pilotRoundCategory = new PilotRoundCategory();
            $pilotRoundCategory->setPilot($pilot)
                ->setRound($round)
                ->setCategory($category)->setVehicle($vehicle)
                ->setMainPilot($isMainPilot)
                ->setSecondPilot($secondPilot);

            $this->em->persist($pilotRoundCategory);
        }

        for ($i = 1; $i < 3; $i++) {
            $qualifying = $this->qualifyingRepository->findOneBy(['pilotRoundCategory' => $pilotRoundCategory, 'passage' => $i]);
            if ($qualifying === null) {
                $qualifying = new Qualifying();
                $qualifying->setPilotRoundCategory($pilotRoundCategory)
                    ->setPassage($i)
                    ->setIsValid(true);

                $this->em->persist($qualifying);
            }
        }
    }
}