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

    public function getPenalties(PilotRoundCategory $pilotRoundCategory): array
    {
        return $this->penaltyRepository->findBy(['pilotRoundCategory' => $pilotRoundCategory]);
    }

    /**
     * @param PilotRoundCategory $pilotRoundCategory
     * @param PenaltyDto[] $penaltiesDto
     * @return array
     */
    public function updatePenalties(PilotRoundCategory $pilotRoundCategory, array $penaltiesDto): array
    {
        $penalties = [];
        foreach ($penaltiesDto as $penaltyDto) {
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
            $penalties[] = $penalty;
        }

        $this->em->flush();

        return $penalties;
    }

    public function deletePenalty(Penalty $penalty): void
    {
        $this->em->remove($penalty);
        $this->em->flush();
    }
}