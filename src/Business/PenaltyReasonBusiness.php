<?php

namespace App\Business;

use App\Dto\PenaltyReasonDto;
use App\Entity\PenaltyReason;
use App\Repository\PenaltyReasonRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class PenaltyReasonBusiness
{
    public function __construct(
        private PenaltyReasonRepository $penaltyReasonRepository,
        private EntityManagerInterface $em,
    )
    {}

    public function getPenaltyReasons(): array
    {
        return $this->penaltyReasonRepository->findAll();
    }

    public function createPenaltyReason(PenaltyReasonDto $penaltyReasonDto): PenaltyReason
    {
        $penaltyReason = $this->penaltyReasonRepository->findOneBy(['name' => $penaltyReasonDto->name]);
        if ($penaltyReason === null) {
            $penaltyReason = new PenaltyReason();
            $penaltyReason->setName($penaltyReasonDto->name);
        }

        $this->em->persist($penaltyReason);
        $this->em->flush();

        return $penaltyReason;
    }

    public function deletePenaltyReason(PenaltyReason $penaltyReason): void
    {
        $this->em->remove($penaltyReason);
        $this->em->flush();
    }
}