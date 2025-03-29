<?php

namespace App\Business;

use App\Entity\Battle;
use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Round;
use App\Repository\BattleRankingPointsRepository;
use App\Repository\BattleRepository;
use App\Repository\BattleVersusRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RankingBusiness
{
    public function __construct(
        private QualifyingBusiness $qualifyingBusiness,
        private BattleBusiness $battleBusiness
    )
    {}

    public function getGlobalRanking(Round $round, Category $category): array
    {
        $qualifyingRanking = $this->qualifyingBusiness->getQualifyingRanking($round, $category);
        $battleRanking = $this->battleBusiness->getBattleRanking($round, $category);

        // sum of points in qualifying and battles for each pilotRoundCategory
        $pointsByPilotRoundCategory = $this->sumPointsByPilotRoundCategory($qualifyingRanking, $battleRanking);

        // sort by total points
        usort($pointsByPilotRoundCategory, fn($a, $b) => $b['points'] <=> $a['points']);

        return $pointsByPilotRoundCategory;
    }

    private function sumPointsByPilotRoundCategory(array $qualifyingRanking, array $battleRanking): array {
        $rankingMerged = array_merge($qualifyingRanking, $battleRanking);

        $grouped = array_reduce($rankingMerged, function ($carry, $entry) {
            $pilotRoundCategoryId = $entry['pilotRoundCategory']->getId();
            $points = $entry['points'];

            if (!isset($carry[$pilotRoundCategoryId])) {
                $carry[$pilotRoundCategoryId] = [
                    'pilotRoundCategory' => $entry['pilotRoundCategory'],
                    'points' => 0
                ];
            }

            $carry[$pilotRoundCategoryId]['points'] += $points;

            return $carry;
        }, []);

        return array_values($grouped);
    }
}