<?php

namespace App\Business;

use App\Dto\PenaltyDto;
use App\Entity\Penalty;
use App\Entity\PilotRoundCategory;
use App\Repository\PenaltyReasonRepository;
use App\Repository\PenaltyRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class PenaltyBusiness
{
    public function __construct(
        private PenaltyRepository $penaltyRepository,
        private PenaltyReasonRepository $penaltyReasonRepository,
        private EntityManagerInterface $em,
    )
    {}

    /**
     * @param PilotRoundCategory $pilotRoundCategory
     * @param PenaltyDto[] $penalties
     * @return void
     */
    public function updatePenalties(PilotRoundCategory $pilotRoundCategory, array $penalties): void
    {
        foreach ($penalties as $penaltyDto) {
            if ($penaltyDto->id === null) {
                $penalty = new Penalty();
            } else {
                $penalty = $this->penaltyRepository->find($penaltyDto->id);
                if ($penalty === null) {
                    continue;
                }
            }

            $penaltyReason = $this->penaltyReasonRepository->find($penaltyDto->penaltyReasonId);
            if ($penaltyReason === null) {
                continue;
            }

            $penalty->setPilotRoundCategory($pilotRoundCategory)
                ->setPoints($penaltyDto->points)
                ->setPenaltyReason($penaltyReason);

            $this->em->persist($penalty);
        }

        $this->em->flush();
    }
}